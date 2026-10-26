<?php

declare(strict_types=1);

namespace Joust;

use Composer\InstalledVersions;

use function Psl\Filesystem\canonicalize;
use function Psl\Type\non_empty_string;

/**
 * @internal
 */
trait HasRootDir
{
    /**
     * @return non-empty-string
     */
    private function rootDir(): string
    {
        static $root;

        if ($root === null) {
            $root = non_empty_string()->assert(canonicalize(InstalledVersions::getRootPackage()['install_path']));
        }

        // @mago-expect analysis:mixed-return-statement
        return $root;
    }

    /**
     * @return non-empty-string
     */
    private function rootPath(string $path): string
    {
        return "{$this->rootDir()}/{$path}";
    }
}
