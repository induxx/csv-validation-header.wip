<?php

namespace Misery\Component\Common\Pipeline;

use Psr\Log\LoggerAwareTrait;
use Misery\Component\Common\Pipeline\Exception\InvalidItemException;
use Misery\Component\Common\Pipeline\Exception\SkipPipeLineException;
use Misery\Component\Debugger\ItemDebugger;
use Misery\Component\Debugger\NullItemDebugger;

class Pipeline
{
    use LoggerAwareTrait;

    /** @var PipeReaderInterface */
    private $input;
    /** @var PipeWriterInterface */
    private $invalid;
    /** @var PipeInterface[] */
    private $pipeLines = [];
    /** @var PipeWriterInterface[] */
    private $outputs = [];
    /** @var NullItemDebugger */
    private $debugger;

    public function input(PipeReaderInterface $reader): self
    {
        $this->input = $reader;
        $this->debugger = new NullItemDebugger();

        return $this;
    }

    public function line(PipeInterface $pipe): self
    {
        $this->pipeLines[] = $pipe;

        return $this;
    }

    public function invalid(PipeWriterInterface $writer): self
    {
        $this->invalid = $writer;

        return $this;
    }

    public function output(PipeWriterInterface $writer): self
    {
        $this->outputs[] = $writer;

        return $this;
    }

    public function runInDebugMode(int $amount = -1, int $lineNumber = -1): void
    {
        $this->debugger = new ItemDebugger();
        $this->run($amount, $lineNumber);
    }

    public function run(int $amount = -1, int $lineNumber = -1): void
    {
        $i = 0;
        // looping
        while ($i !== $amount && $item = $this->input->read()) {
            $i++;
            if ($i !== $lineNumber && $lineNumber !== -1) {
                continue;
            }
            $this->debugger->log($item, 'original item');
            try {
                foreach ($this->pipeLines as $pipeLine) {
                    $item = $pipeLine->pipe($item);
                }
                foreach ($this->outputs as $output) {
                    $output->write($item);
                }
            } catch (SkipPipeLineException $exception) {
                if (!empty($exception->getMessage())) {
                    $this->logger->info(sprintf('Skipped: %s', $exception->getMessage()));
                }
                continue;
            } catch (InvalidItemException $exception) {
                $this->invalid->write([
                    'line' => $i,
                    'msg' => $exception->getMessage(),
                    'item' => json_encode($exception->getInvalidItem()),
                ]);
                // WE need a silent LOGGER here
                //$this->logger->error($exception->getMessage(), $exception->getInvalidItem());
                continue;
            }
            if ($i === $lineNumber) {
                break;
            }
        }
        unset($output);

        // stopping
        $this->input->stop();

        foreach ($this->outputs as $output) {
            try {
                $output->stop();
            } catch (SkipPipeLineException $exception) {
                continue;

            } catch (InvalidItemException $exception) {
                $this->invalid->write([
                    'line' => $i,
                    'msg' => $exception->getMessage(),
                    'item' => json_encode($exception->getInvalidItem()),
                ]);
                $this->logger->error($exception->getMessage());
                continue;
            }
        }
    }
}