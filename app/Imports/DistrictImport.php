<?php

namespace App\Imports;

use App\Models\District;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DistrictImport implements ToCollection, WithHeadingRow
{
    protected $stateId;

    public function __construct($stateId)
    {
        $this->stateId = $stateId;
    }

    // Define how to process each row of data
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Skip if no data in the row
            if (empty($row['districtname'])) {
                continue;
            }

            // Check if the district already exists
            $existingDistrict = District::where('district_name', $row['districtname'])
                ->where('state_id', $this->stateId)
                ->first();

            if (!$existingDistrict) {
                // Insert new district if it does not exist
                District::create([
                    'district_name' => $row['districtname'],
                    'state_id' => $this->stateId,
                    // Add other necessary fields based on your database schema
                ]);
            }
        }
    }
}
