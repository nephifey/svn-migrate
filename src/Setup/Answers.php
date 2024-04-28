<?php
/*
 * This file is part of nephifey/svn-migrate.
 *
 * (c) Nathan Phifer <nephifer5@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nephifey\SvnMigrate\Setup;

final class Answers {

    private ?string $svnRepositoryUrl = null;

    private string $svnTrunk = "/trunk";

    private string $svnBranches = "/branches";

    private string $svnTags = "/tags";

    private ?string $svnUsername = null;

    private ?string $svnPassword = null;

    private bool $metadata = false;

    private string $gitPrefix = "origin/";

    private ?string $outputDestination = null;

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setValue(string $name, $value): void {
        if (!property_exists($this, $name)) {
            return;
        }

        $magicMethod = "set" . ucfirst($name);
        if (method_exists($this, $magicMethod)) {
            $this->$magicMethod($value);
        } else {
            $this->$name = $value;
        }
    }

    public function setSvnTrunk(string $trunk): void {
        $this->svnTrunk = $this->normalizePath($trunk);
    }

    public function setSvnBranches(string $trunk): void {
        $this->svnBranches = $this->normalizePath($trunk);
    }

    public function setSvnTags(string $trunk): void {
        $this->svnTags = $this->normalizePath($trunk);
    }

    public function setGitPrefix(string $gitPrefix): void {
        $this->gitPrefix = $this->normalizePath($gitPrefix, false);
    }

    public function getSvnRepositoryUrl(): ?string {
        return $this->svnRepositoryUrl;
    }

    public function getSvnTrunk(): string {
        return $this->svnTrunk;
    }

    public function getSvnBranches(): string {
        return $this->svnBranches;
    }

    public function getSvnTags(): string {
        return $this->svnTags;
    }

    public function getSvnUsername(): ?string {
        return $this->svnUsername;
    }

    public function getSvnPassword(): ?string {
        return $this->svnPassword;
    }

    public function hasMetadata(): bool {
        return $this->metadata;
    }

    public function getGitPrefix(): string {
        return $this->gitPrefix;
    }

    public function getOutputDestination(): ?string {
        return $this->outputDestination;
    }

    private function normalizePath(string $path, bool $prefix = true): string {
        $path = trim($path, " \n\r\t\v\0/");

        return (
            $prefix
            ? "/{$path}"
            : "{$path}/"
        );
    }
}
