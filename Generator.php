<?php

namespace NetworkRailBusinessSystems\Badges;

class Generator
{
    const CHARACTER_WIDTH = 8;
    
    public function __construct()
    {
        $this->makeComposerBadge();
        $this->makeCoverageBadge();
        $this->makeFrontendBadge();
        $this->makeLaravelBadge();
        $this->makeNpmBadge();
        $this->makePhpBadge();
        $this->makeTestsBadge();
        $this->makeViewsBadge();
    }

    public function makeComposerBadge(): void
    {
        $output = [];

        exec('composer outdated --direct 2>&1', $output);
        $count = count($output) - 3;

        if ($count === -2) {
            $value = 'Up to date';
            $status = 1;
        } elseif ($count <= 3) {
            $value = "$count outdated";
            $status = 0;
        } else {
            $value = "$count outdated";
            $status = -1;
        }

        $this->makeBadge('Composer', $value, $status, 'composer');
    }

    public function makeCoverageBadge(): void
    {
        $path = $this->getPath('.phpunit.cache/coverage.txt');

        if (file_exists($path) === false) {
            $result = 'Unknown';
            $status = -1;

        } else {
            $file = fopen($path, 'r');
            fgets($file);
            fgets($file);
            fgets($file);
            $line = fgets($file);
            fclose($file);

            $result = (int) trim(
                substr($line, strpos($line, 'Classes: ') + 9, 3),
                '.',
            );

            $status = match (true) {
                $result >= 100 => 1,
                $result >= 80 => 0,
                default => -1,
            };

            $result .= '%';
        }

        $this->makeBadge('Coverage', $result, $status, 'coverage');
    }
    
    public function makeFrontendBadge(): void
    {
        $path = $this->getPath('composer.json');

        if (file_exists($path) === false) {
            $frontend = 'Unknown';
            $status = -1;

        } else {
            $requirements = json_decode(
                file_get_contents($path),
                true,
            )['require'] ?? [];

            if (isset($requirements['anthonyedmonds/govuk-laravel']) === true) {
                $frontend = 'GOV.UK';
                $status = 1;
            } elseif (isset($requirements['livewire/livewire']) === true) {
                $frontend = 'Livewire';
                $status = 0;
            } else {
                $frontend = 'Vue';
                $status = -1;
            }
        }

        $this->makeBadge('Frontend', $frontend, $status, 'frontend');
    }
    
    public function makeLaravelBadge(): void
    {
        $path = $this->getPath('composer.json');

        if (file_exists($path) === false) {
            $laravel = 'Unknown';
            $status = -1;

        } else {
            $laravel = json_decode(
                file_get_contents($path),
                true,
            )['require']['laravel/framework'] ?? 'Unknown';

            $laravel = trim($laravel, '^');

            $status = match ($laravel) {
                '12' => 1,
                '11' => 0,
                default => -1,
            };
        }

        $this->makeBadge('Laravel', $laravel, $status, 'laravel');
    }
    
    public function makeNpmBadge(): void
    {
        $output = [];

        exec('npm outdated 2>&1', $output);
        $count = count($output) - 1;

        if ($count === -1) {
            $value = 'Up to date';
            $status = 1;
        } elseif ($count <= 3) {
            $value = "$count outdated";
            $status = 0;
        } else {
            $value = "$count outdated";
            $status = -1;
        }

        $this->makeBadge('NPM', $value, $status, 'npm');
    }
    
    public function makePhpBadge(): void
    {
        $path = $this->getPath('composer.json');

        if (file_exists($path) === false) {
            $php = 'Unknown';
            $status = -1;

        } else {
            $php = json_decode(
                file_get_contents($path),
                true,
            )['require']['php'] ?? 'Unknown';

            $php = trim($php, '^');

            $status = match ($php) {
                '8.4' => 1,
                '8.3' => 0,
                default => -1,
            };
        }

        $this->makeBadge('PHP', $php, $status, 'php');
    }
    
    public function makeTestsBadge(): void
    {
        $path = $this->getPath('.phpunit.cache/tests.xml');

        if (file_exists($path) === false) {
            $label = 'Unknown';
            $status = -1;

        } else {
            $file = fopen($path, 'r');
            fgets($file);
            fgets($file);
            $line = fgets($file);
            fclose($file);

            $errors = (int) substr($line, strpos($line, 'errors="') + 8, 1);
            $failures = (int) substr($line, strpos($line, 'failures="') + 10, 1);

            if ($errors === 0 && $failures === 0) {
                $label = 'Pass';
                $status = 1;
            } else {
                $label = 'Fail';
                $status = -1;
            }
        }

        $this->makeBadge('Tests', $label, $status, 'tests');
    }

    public function makeViewsBadge(): void
    {
        $path = $this->getPath('.phpunit.cache/view-results.json');

        if (file_exists($path) === false) {
            return;
        }

        $raw = file_get_contents($path);
        $json = json_decode($raw);

        $this->makeBadge(
            'Views',
            "$json->percent%",
            $json->result === 'Pass' ? 1 : -1,
            'views',
        );
    }
    
    protected function makeBadge(
        string $label,
        string $value,
        int    $status,
        string $filename,
    ): void {
        $labelWidth = (strlen($label) * self::CHARACTER_WIDTH) + 4;
        $valueWidth = (strlen($value) * self::CHARACTER_WIDTH) + 4;
        $fullWidth = $labelWidth + $valueWidth;

        $colour = match ($status) {
            1 => '00703c',
            0 => 'f47738',
            default => 'd4351c',
        };

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$fullWidth.'" height="20" role="img" aria-label="'.$label.': '.$value.'">
            <title>'.$label.': '.$value.'</title>
            
            <linearGradient id="s" x2="0" y2="100%">
                <stop offset="0" stop-color="#bbb" stop-opacity=".1"/>
                <stop offset="1" stop-opacity=".1"/>
            </linearGradient>
            
            <clipPath id="r">
                <rect width="'.$fullWidth.'" height="20" rx="3" fill="#fff"/>
            </clipPath>
            
            <g clip-path="url(#r)">
                <rect width="'.$labelWidth.'" height="20" fill="#555"/>
                <rect x="'.$labelWidth.'" width="'.$valueWidth.'" height="20" fill="#'.$colour.'"/>
                <rect width="'.$fullWidth.'" height="20" fill="url(#s)"/>
            </g>
            
            <g fill="#ffffff" font-family="Consolas, monospace" font-size="12">
                <text aria-hidden="true" x="'.($labelWidth / 2).'" y="15" fill="#010101" fill-opacity=".3" text-anchor="middle">'.$label.'</text>
                <text x="'.($labelWidth / 2).'" y="14" fill="#ffffff" text-anchor="middle">'.$label.'</text>
                
                <text aria-hidden="true" x="'.($labelWidth + ($valueWidth / 2)).'" y="15" fill="#010101" fill-opacity=".3" text-anchor="middle">'.$value.'</text>
                <text x="'.($labelWidth + ($valueWidth / 2)).'" y="14" fill="#ffffff" text-anchor="middle">'.$value.'</text>
            </g>
        </svg>';

        file_put_contents($this->getPath(".github/$filename.svg"), $svg);
    }
    
    protected function getPath(string $path): string
    {
        return __DIR__.'/../../../'.$path;
    }
}
