<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class EmployeeImport implements ToArray
{

    protected array $processedData = [];

    public static function fields(): array
    {
        return array(
            array('id' => 'name', 'name' => __('modules.cleaners.employeeName'), 'required' => 'Yes',),
            array('id' => 'email', 'name' => __('modules.cleaners.employeeEmail'), 'required' => 'Yes',),
            array('id' => 'mobile', 'name' => __('app.mobile'), 'required' => 'No',),
            array('id' => 'gender', 'name' => __('modules.cleaners.gender'), 'required' => 'No',),
            array('id' => 'employee_id', 'name' => __('modules.cleaners.employeeId'), 'required' => 'Yes'),
            array('id' => 'joining_date', 'name' => __('modules.cleaners.joiningDate'), 'required' => 'Yes'),
            array('id' => 'address', 'name' => __('app.address'), 'required' => 'No'),
            array('id' => 'hourly_rate', 'name' => __('modules.cleaners.hourlyRate'), 'required' => 'No'),
        );
    }

    public function array(array $array): array
    {
        $this->processedData = $array;
        return $array;
    }

    public function getProcessedData(): array
    {
        return $this->processedData;
    }

}
