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
     * @param \OpenSoutheners\LaravelModelStatus\ModelStatus|mixed $status
     * @return bool
     */
    public function hasStatus($status): bool;

    /**
     * Set status from enum instance.
     * 
     * @param \OpenSoutheners\LaravelModelStatus\ModelStatus $status
     * @param bool $saving
     * @throws \Exception 
     * @return self|bool
     */
    public function setStatus(ModelStatus $status, bool $saving);
    
    /**
     * Set status when given value matches current status.
     * 
     * @param \OpenSoutheners\LaravelModelStatus\ModelStatus $current
     * @param \OpenSoutheners\LaravelModelStatus\ModelStatus $value
     * @param bool $saving
     * @throws \Exception 
     * @return self|bool
     */
    public function setStatusWhen($current, $value, bool $saving);
}
