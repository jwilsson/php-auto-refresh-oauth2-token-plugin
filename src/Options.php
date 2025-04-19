<?php

declare(strict_types=1);

namespace JWilsson;

class Options
{
    /**
     * @var int
     */
    public int $threshold = 300; // 5 minutes

    /**
     * Constructor.
     *
     * @param array<string, mixed> $options Options for the plugin.
     */
    public function __construct(array $options)
    {
        $this->setThreshold($options);
    }

    /**
     * Set the threshold.
     *
     * @param array<string, mixed> $options Options.
     *
     * @return void
     */
    protected function setThreshold(array $options): void
    {
        $threshold = $options['threshold'] ?? $this->threshold;
        if (!is_int($threshold)) {
            throw new \InvalidArgumentException('Threshold must be an integer, received ' . gettype($threshold));
        }

        $this->threshold = $threshold;
    }
}
