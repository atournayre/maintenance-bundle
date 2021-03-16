<?php

namespace Atournayre\MaintenanceBundle\Service;

use Exception;
use SplFileObject;

class EnvFileService
{
    /**
     * @var SplFileObject|null
     */
    private $file;

    /**
     * @var array
     */
    private $dotEnvVariables = [];

    public function load(string $filePath): void
    {
        $this->dotEnvVariables = @include $filePath;
        $this->file = new SplFileObject($filePath, 'r+');
    }

    public function add(string $key, ?string $value)
    {
        $this->dotEnvVariables[$key] = $value;
    }

    public function reset(string $key)
    {
        $this->dotEnvVariables[$key] = '';
    }

    public function save(): int
    {
        $content = $this->arrayToContent($this->dotEnvVariables);

        $this->file->rewind();
        $this->file->ftruncate(0);
        return $this->file->fwrite($content);
    }

    public function get($key): string
    {
        if (!array_key_exists($key, $this->dotEnvVariables)) {
            throw new Exception(sprintf('%s is missing in .env file!', $key));
        }
        return $this->dotEnvVariables[$key];
    }

    private function arrayToContent(array $array): string
    {
        $content = '<?php' . PHP_EOL;
        $content .= 'return array(' . PHP_EOL;
        foreach ($array as $key => $value) {
            $content .= sprintf($this->getOutputPatternForValue($value), $key, $value);
        }
        $content .= ');' . PHP_EOL;
        return $content;
    }

    private function getOutputPatternForValue($value)
    {
        $outputPattern = '\'%s\' => \'%s\',';
        if ($value === 'true' || $value === true) {
            $outputPattern = '\'%s\' => true,';
        } elseif ($value === 'false' || $value === false) {
            $outputPattern = '\'%s\' => false,';
        } elseif (is_null($value)) {
            $outputPattern = '\'%s\' => null,';
        }

        return '    ' . $outputPattern . PHP_EOL;
    }
}

