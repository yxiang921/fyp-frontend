<?php

namespace Database\Seeders;

use App\Models\MissingPerson;
use App\Models\MissingPersonPhoto;
use App\Models\MissingPersonTimeline;
use Illuminate\Database\Seeder;

class MissingPersonSeeder extends Seeder
{
    public function run(): void
    {
        $persons = [
            ['MP-2024-001', 'Nurul Ain binti Ahmad', 'female', '1995-03-12', '950312-01-5522', 'Jalan Tun Razak, Kuala Lumpur', '2024-01-15', 'active', 'Last seen wearing blue baju kurung near KLCC. 160cm tall, slim build, fair complexion, long black hair. Small mole on left cheek.'],
            ['MP-2024-002', 'Muhammad Hafiz bin Ismail', 'male', '1990-07-22', '900722-14-2233', 'Jalan Bukit Bintang, Kuala Lumpur', '2024-01-20', 'active', '170cm tall, medium build, short black hair. Last seen wearing black t-shirt and jeans. Has a scar on right eyebrow.'],
            ['MP-2024-003', 'Tan Mei Ling', 'female', '1988-11-05', '881105-01-4488', 'Jalan Sultan Ismail, Kuala Lumpur', '2024-02-01', 'closed', '165cm tall, fair complexion, shoulder-length dyed brown hair. Last seen near Lot 10 shopping mall.'],
            ['MP-2024-004', 'Ahmad Farhan bin Zakaria', 'male', '2005-06-18', '050618-03-1199', 'Georgetown, Penang', '2024-02-10', 'active', '175cm tall, student. Last seen in school uniform near Komtar. Wears glasses.'],
            ['MP-2024-005', 'Siti Khadijah binti Rahman', 'female', '1978-09-30', '780930-02-3377', 'Johor Bahru Sentral', '2024-02-18', 'found', '155cm tall, medium build. Last seen at JB Sentral bus terminal. Found on 2024-03-20.'],
            ['MP-2024-006', 'Rajesh a/l Muthu', 'male', '1985-04-15', '850415-07-5566', 'Seremban Terminal 1', '2024-03-01', 'active', '180cm tall, dark complexion, bald. Last seen at Seremban bus terminal. Distinctive tattoo on left forearm.'],
            ['MP-2024-007', 'Nurul Huda binti Hassan', 'female', '2002-12-25', '021225-11-8899', 'Kuala Terengganu Bus Terminal', '2024-03-12', 'pending', '158cm tall, fair complexion, wears hijab. Last seen with a red backpack. Submitted by public user.'],
            ['MP-2024-008', 'Wong Chen Keong', 'male', '1970-08-08', '700808-08-1122', 'Kuching Waterfront', '2024-03-20', 'reopened', '168cm tall, grey hair, slim build. Elderly gentleman last seen at Kuching Waterfront.'],
            ['MP-2024-009', 'Aisyah binti Omar', 'female', '1998-01-20', '980120-10-3344', 'Shah Alam Section 7', '2024-04-01', 'pending', '162cm tall, fair complexion. Last seen leaving her apartment complex. Submitted by public user.'],
            ['MP-2024-010', 'Mohd Zaki bin Abdullah', 'male', '1982-05-28', '820528-12-6655', 'Kota Kinabalu Waterfront', '2024-04-15', 'active', '172cm tall, medium build. Last seen at night market. Wears a silver chain necklace.'],
        ];

        // Generate 50 missing persons (10 manual + 40 generated)
        $firstNames = ['Amir', 'Farah', 'Hakim', 'Zulaikha', 'Danish', 'Alya', 'Hariz', 'Sofea', 'Imran', 'Batrisyia',
            'Khairul', 'Aina', 'Syafiq', 'Maisarah', 'Afiq', 'Damia', 'Irfan', 'Amani', 'Rizqi', 'Izzati',
            'Luqman', 'Fatin', 'Najmi', 'Husna', 'Aliff', 'Atiqah', 'Shahmi', 'Nabila', 'Adam', 'Sarah'];

        $lastNames = ['bin Ahmad', 'binti Ismail', 'bin Hassan', 'binti Rahman', 'bin Yusof', 'binti Abdullah',
            'bin Zainal', 'binti Kamaruddin', 'bin Rosli', 'binti Hussein', 'a/l Muthusamy', 'a/p Krishnan',
            'bin Sulaiman', 'binti Othman', 'bin Rashid', 'a/l Govindasamy', 'anak Jelani', 'bin Ibrahim'];

        $locations = ['Kuala Lumpur', 'Petaling Jaya', 'Shah Alam', 'Johor Bahru', 'Penang', 'Ipoh',
            'Seremban', 'Melaka', 'Kuantan', 'Kota Kinabalu', 'Kuching', 'Alor Setar', 'Kuala Terengganu'];

        // Mix of statuses including pending (for public submissions)
        $statuses = ['active', 'active', 'active', 'active', 'active', 'pending', 'pending', 'found', 'closed', 'reopened'];

        for ($i = 0; $i < 40; $i++) {
            $fn = $firstNames[$i % count($firstNames)];
            $ln = $lastNames[$i % count($lastNames)];
            $fullName = $fn . ' ' . $ln;
            $idx = $i + 11;
            $caseNum = 'MP-2024-' . str_pad($idx, 3, '0', STR_PAD_LEFT);
            $status = $statuses[$i % count($statuses)];

            // Pending cases are submitted by public users (IDs 9-10)
            // Active/other cases by PDRM officers (IDs 2-6)
            $createdBy = $status === 'pending' ? rand(9, 10) : rand(2, 6);

            MissingPerson::create([
                'case_number' => $caseNum,
                'full_name' => $fullName,
                'gender' => $i % 2 == 0 ? 'male' : 'female',
                'date_of_birth' => now()->subYears(rand(8, 80))->subDays(rand(1, 365)),
                'national_id' => str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT) . str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT) . str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT) . '-01-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                'last_seen_location' => $locations[array_rand($locations)],
                'last_seen_date' => now()->subDays(rand(15, 180))->format('Y-m-d'),
                'status' => $status,
                'description' => $status === 'pending' ? 'Submitted by public user. Awaiting officer verification.' : 'Missing person case. Further details available in case file.',
                'created_by' => $createdBy,
            ]);
        }

        // Add the manual records
        foreach ($persons as $p) {
            // Manual pending cases are from public users, others from PDRM officers
            $createdBy = $p[7] === 'pending' ? rand(9, 10) : rand(2, 6);

            MissingPerson::create([
                'case_number' => $p[0],
                'full_name' => $p[1],
                'gender' => $p[2],
                'date_of_birth' => $p[3],
                'national_id' => $p[4],
                'last_seen_location' => $p[5],
                'last_seen_date' => $p[6],
                'status' => $p[7],
                'description' => $p[8],
                'created_by' => $createdBy,
            ]);
        }
    }
}
