<?php

namespace App\Reports;

abstract class ReportProvider
{
    abstract public function slug(): string;

    abstract public function label(): string;

    abstract public function description(): string;

    abstract public function icon(): ?string;

    abstract public function reports(): array;
}
