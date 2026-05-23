<?php

namespace App\Services\System;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;

class GitVersionService
{
    public function resolve(): string
    {
        $fromGit = $this->fromGitDescribe();

        if ($fromGit !== '') {
            return $fromGit;
        }

        return $this->fromGitLabTagsApi() ?? '';
    }

    private function fromGitDescribe(): string
    {
        if (! is_dir(base_path('.git'))) {
            return '';
        }

        $process = new Process(
            ['git', '-c', 'safe.directory='.base_path(), 'describe', '--tags'],
            base_path()
        );
        $process->setTimeout(15);
        $process->run();

        if (! $process->isSuccessful()) {
            return '';
        }

        return trim($process->getOutput());
    }

    private function fromGitLabTagsApi(): ?string
    {
        $token = config('git.token');

        if (! $token) {
            return null;
        }

        $response = Http::withHeaders([
            'PRIVATE-TOKEN' => $token,
        ])->get(config('git.project_tags_url'), [
            'per_page' => 1,
        ]);

        if (! $response->successful()) {
            return null;
        }

        $tags = $response->json();

        if (! is_array($tags) || count($tags) === 0) {
            return null;
        }

        return $tags[0]['name'] ?? null;
    }
}
