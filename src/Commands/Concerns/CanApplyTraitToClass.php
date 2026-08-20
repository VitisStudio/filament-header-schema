<?php

namespace VitisStudio\FilamentHeaderSchema\Commands\Concerns;

use Illuminate\Filesystem\Filesystem;

/**
 * Adds a trait to an existing class file.
 *
 * Filament's own generators only ever write new files — `make:filament-page`
 * tells you to register the page yourself rather than editing the resource.
 * Applying the trait by hand on every page is the busywork this command exists
 * to remove, so the edit is done textually and as narrowly as possible: one
 * `use` import and one `use` statement in the class body, with the rest of the
 * file, its formatting and its comments left exactly as they were.
 */
trait CanApplyTraitToClass
{
    /**
     * @param  class-string  $traitFqn
     * @return bool Whether the file was modified.
     */
    protected function applyTraitToClassFile(string $path, string $traitFqn): bool
    {
        $filesystem = app(Filesystem::class);

        $contents = $filesystem->get($path);
        $basename = class_basename($traitFqn);

        // Windows checkouts, and repositories that force CRLF through
        // `.gitattributes`, hand us `\r\n`. Every `$` anchor below would then
        // fail to match, because PCRE's multiline `$` sits before the `\n` and
        // the `\r` is in the way — the import would be skipped silently and the
        // trait statement written with the wrong ending. Normalize for the edit
        // and restore the file's own ending on the way out. A file that mixes
        // both endings is settled on the one it uses first.
        $ending = (preg_match('/\r\n|\n/', $contents, $match) === 1) ? $match[0] : "\n";
        $contents = str_replace("\r\n", "\n", $contents);

        if ($this->classBodyUsesTrait($contents, $basename)) {
            return false;
        }

        $contents = $this->insertImport($contents, $traitFqn);
        $contents = $this->insertTraitUse($contents, $basename);

        if ($ending !== "\n") {
            $contents = str_replace("\n", $ending, $contents);
        }

        $filesystem->put($path, $contents);

        return true;
    }

    protected function classBodyUsesTrait(string $contents, string $basename): bool
    {
        $body = $this->afterClassOpeningBrace($contents);

        return $body !== null
            && preg_match('/^\s*use\s+[^;]*\b'.preg_quote($basename, '/').'\b[^;]*;/m', $body) === 1;
    }

    /**
     * Adds the import in alphabetical order among the existing ones, which is
     * where Pint would put it anyway.
     */
    protected function insertImport(string $contents, string $traitFqn): string
    {
        if (preg_match('/^use\s+'.preg_quote($traitFqn, '/').'\s*;$/m', $contents) === 1) {
            return $contents;
        }

        $classOffset = $this->classOpeningBraceOffset($contents);
        $import = "use {$traitFqn};";

        preg_match_all('/^use\s+([^;]+);$/m', $contents, $matches, PREG_OFFSET_CAPTURE);

        $imports = array_filter(
            $matches[0],
            fn (array $match): bool => $classOffset === null || $match[1] < $classOffset,
        );

        foreach ($imports as $match) {
            if (strcasecmp($match[0], $import) < 0) {
                continue;
            }

            return substr_replace($contents, $import."\n", $match[1], 0);
        }

        if ($imports) {
            $last = end($imports);

            return substr_replace($contents, "\n".$import, $last[1] + strlen($last[0]), 0);
        }

        // No imports at all: the namespace declaration is the only anchor.
        if (preg_match('/^namespace\s+[^;]+;$/m', $contents, $match, PREG_OFFSET_CAPTURE) === 1) {
            $offset = $match[0][1] + strlen($match[0][0]);

            return substr_replace($contents, "\n\n".$import, $offset, 0);
        }

        return $contents;
    }

    protected function insertTraitUse(string $contents, string $basename): string
    {
        $offset = $this->classOpeningBraceOffset($contents);

        if ($offset === null) {
            return $contents;
        }

        $body = substr($contents, $offset);
        $statement = "    use {$basename};";

        // Sit alongside any traits the class already uses, in alphabetical order.
        if (preg_match_all('/\G\s*\n(\s*use\s+[^;]+;)/', $body, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[1] as $match) {
                if (strcasecmp(trim($match[0]), trim($statement)) < 0) {
                    continue;
                }

                return substr_replace($contents, trim($statement)."\n    ", $offset + $match[1], 0);
            }

            $last = end($matches[1]);

            return substr_replace($contents, "\n".$statement, $offset + $last[1] + strlen($last[0]), 0);
        }

        return substr_replace($contents, "\n".$statement."\n", $offset, 0);
    }

    protected function afterClassOpeningBrace(string $contents): ?string
    {
        $offset = $this->classOpeningBraceOffset($contents);

        return $offset === null ? null : substr($contents, $offset);
    }

    /**
     * The offset just past the class declaration's opening brace.
     */
    protected function classOpeningBraceOffset(string $contents): ?int
    {
        if (preg_match('/^(final\s+|abstract\s+|readonly\s+)*class\s+\w+[^{]*\{/m', $contents, $match, PREG_OFFSET_CAPTURE) !== 1) {
            return null;
        }

        return $match[0][1] + strlen($match[0][0]);
    }
}
