<?php
namespace App\Imports;

use App\Models\Pincode;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PincodeImport implements ToModel, WithHeadingRow
{
    private $districtId;

    public function __construct($districtId)
    {
        $this->districtId = $districtId;
    }

    public function model(array $row)
    {
       
        $pincodeValue = $row['pincode']; 
        if (Pincode::where('pincode', $pincodeValue)->exists()) {
            return null; 
        }
        return new Pincode([
            'pincode' => $pincodeValue,
            'postname' => $row['postname'],
            'district_id' => $this->districtId
        ]);
    }
}
