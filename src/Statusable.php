<?php

namespace OpenSoutheners\LaravelModelStatus;

/**
 * @property \OpenSoutheners\LaravelModelStatus\ModelStatus $status
 * @property-read array<string> $statuses
 */
interface Statusable
{
    /**
     * Get array of statuses cases from enum.
     *
     * @return array<\OpenSoutheners\LaravelModelStatus\ModelStatus>
     */
    public function getAllStatuses(): array;

    /**
     * Check model current status equals introduced.
     *
     * @param  \OpenSoutheners\LaravelModelStatus\ModelStatus|mixed  $status
     */
    public function hasStatus($status): bool;

    /**
     * Set status from enum instance.
     *
     * @return self|bool
     *
     * @throws \Exception
     */
    public function setStatus(ModelStatus $status, bool $saving);

    /**
     * Set status when given value matches current status.
     *
     * @param  \OpenSoutheners\LaravelModelStatus\ModelStatus  $current
     * @param  \OpenSoutheners\LaravelModelStatus\ModelStatus  $value
     * @return self|bool
     *
     * @throws \Exception
     */
    public function setStatusWhen($current, $value, bool $saving);

    /**
     * Get model default status.
     *
     * @return \OpenSoutheners\LaravelModelStatus\ModelStatus|null
     */
    public function defaultStatus();
}
