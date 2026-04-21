<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleCategory;
use App\Models\VehicleBrand;
use App\Models\VehicleModel;
use App\Models\College;
use App\Models\Course;

class FleetAssetsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Categories
        $categoriesData = [
            'Motorcycle' => ['icon' => 'bicycle'],
            'Car / Sedan' => ['icon' => 'car'],
            'SUV / Van' => ['icon' => 'jeep'],
            'Pickup' => ['icon' => 'truck'],
            'Truck' => ['icon' => 'car-profile'],
            'Sidecar Motorcycles' => ['icon' => 'motorcycle'],
        ];

        $categoryModels = [];
        foreach ($categoriesData as $name => $data) {
            $categoryModels[$name] = VehicleCategory::updateOrCreate(['name' => $name], $data);
        }

        // 2. Brands, Categories & Models
        // Grouped by commonly associated categories
        $brandsData = [
            'Toyota' => [
                'categories' => ['Car / Sedan', 'SUV / Van', 'Pickup'],
                'models' => ['Vios', 'Hilux', 'Fortuner', 'Wigo', 'Innova', 'Camry', 'Raize']
            ],
            'Honda' => [
                'categories' => ['Car / Sedan', 'SUV / Van', 'Motorcycle', 'Sidecar Motorcycles'],
                'models' => ['Civic', 'CR-V', 'City', 'BR-V', 'ADV 160', 'Click 125i', 'PCX 160']
            ],
            'Mitsubishi' => [
                'categories' => ['Car / Sedan', 'SUV / Van', 'Pickup'],
                'models' => ['Montero Sport', 'Mirage', 'L300', 'Xpander', 'Strada']
            ],
            'Nissan' => [
                'categories' => ['Car / Sedan', 'SUV / Van', 'Pickup'],
                'models' => ['Navara', 'Terra', 'Almera', 'Urvan']
            ],
            'Suzuki' => [
                'categories' => ['Car / Sedan', 'SUV / Van', 'Motorcycle', 'Sidecar Motorcycles'],
                'models' => ['Ertiga', 'Jimny', 'Swift', 'S-Presso', 'Burgman Street', 'Raider R150']
            ],
            'Yamaha' => [
                'categories' => ['Motorcycle', 'Sidecar Motorcycles'],
                'models' => ['NMAX', 'Aerox', 'Mio i 125', 'YZF-R15', 'Sniper 155']
            ],
            'Isuzu' => [
                'categories' => ['SUV / Van', 'Pickup', 'Truck'],
                'models' => ['D-MAX', 'mu-X', 'Elf', 'Forward']
            ],
            'Ford' => [
                'categories' => ['SUV / Van', 'Pickup'],
                'models' => ['Ranger', 'Everest', 'Territory', 'Explorer']
            ],
            'Hyundai' => [
                'categories' => ['Car / Sedan', 'SUV / Van'],
                'models' => ['Staria', 'Accent', 'Verna', 'Tucson', 'Creta']
            ],
        ];

        foreach ($brandsData as $brandName => $data) {
            $brand = VehicleBrand::updateOrCreate(['name' => $brandName]);
            
            // Sync categories via pivot
            $catIds = [];
            foreach ($data['categories'] as $catName) {
                if (isset($categoryModels[$catName])) {
                    $catIds[] = $categoryModels[$catName]->id;
                }
            }
            $brand->categories()->sync($catIds);

            // Create models
            foreach ($data['models'] as $modelName) {
                VehicleModel::updateOrCreate([
                    'vehicle_brand_id' => $brand->id,
                    'name' => $modelName
                ]);
            }
        }

        // 3. Academic Data (Updated)
        $departments = [
            'Department of Computer Studies' => [
                'Bachelor of Science in Information Technology (BSIT)'
            ],
            'Department of Teacher Education' => [
                'Bachelor of Elementary Education (BEED)',
                'Bachelor of Secondary Education (BSEd) major in Mathematics',
                'Bachelor of Secondary Education (BSEd) major in Science',
                'Bachelor of Physical Education (BPEd)',
                'Bachelor of Technical-Vocational Teacher Education (BTVTEd)',
                'Diploma in Teaching Secondary (DTS)'
            ],
            'Department of Business Management' => [
                'Bachelor of Science in Hospitality Management (BSHM)'
            ],
            'Department of Engineering' => [
                'Bachelor of Science in Civil Engineering (BSCE)',
                'Bachelor of Science in Electrical Engineering (BSEE)',
                'Bachelor of Science in Mechanical Engineering (BSME)'
            ],
            'Department of Industrial Technology' => [
                'Bachelor of Industrial Technology (BIT) major in Culinary Arts (CA)',
                'Bachelor of Industrial Technology (BIT) major in Electronics (ET)'
            ],
        ];

        foreach ($departments as $deptName => $courses) {
            $college = College::updateOrCreate(
                ['name' => $deptName],
                ['category' => 'academic']
            );
            foreach ($courses as $courseName) {
                Course::updateOrCreate([
                    'college_id' => $college->id,
                    'name' => $courseName
                ]);
            }
        }

        // 4. Administrative Offices
        $offices = [
            'Office of the Campus Director',
            'Registrar Office',
            'Administrative and Finance Services',
            'Human Resource Management Office (HRMO)',
            'Guidance Office',
            'Student Affairs and Services Offices (SASO)',
            'Alumni Relations and Affairs Office',
            'Maintenance and Engineering Office',
            'Library',
            'Campus Clinic',
            'Supply Office',
        ];

        foreach ($offices as $officeName) {
            College::updateOrCreate(
                ['name' => $officeName],
                ['category' => 'administrative', 'code' => 'OFFICE']
            );
        }
    }
}
