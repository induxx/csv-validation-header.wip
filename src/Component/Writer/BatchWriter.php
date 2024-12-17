<?php

namespace Misery\Component\Writer;

class BatchWriter implements ItemWriterInterface
{
    private int $batchSize;
    private int $currentBatchIndex = 1;
    private int $currentBatchCount = 0;
    private string $baseFilename;
    private string $extension;
    private ?ItemWriterInterface $writer = null;
    private array $options;

    public function __construct(private readonly string $className, string $filename, int $batchSize, array $options = [])
    {
        $this->batchSize = $batchSize;
        $this->options = $options;

        // Parse the base filename and extension
        $pathInfo = pathinfo($filename);
        $this->baseFilename = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'];
        $this->extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';

        $this->openNewWriter();
    }

    private function openNewWriter(): void
    {
        // Generate the batch filename
        $batchFilename = sprintf('%s-%d%s', $this->baseFilename, $this->currentBatchIndex, $this->extension);
        $this->writer = new $this->className($batchFilename, $this->options);
        $this->currentBatchCount = 0;
    }

    public function write(array $data, bool $loopItem = true): void
    {
        $this->writer->write($data, $loopItem);
        $this->currentBatchCount++;

        // Check if batch size is reached
        if ($this->currentBatchCount >= $this->batchSize) {
            $this->writer->close();
            $this->currentBatchIndex++;
            $this->openNewWriter();
        }
    }

    public function close(): void
    {
        if ($this->writer) {
            $this->writer->close();
            $this->writer = null;
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}
