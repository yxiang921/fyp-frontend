<?php

namespace Database\Seeders;

use App\Models\Camera;
use Illuminate\Database\Seeder;

class CameraSeeder extends Seeder
{
    public function run(): void
    {
        $cameras = [
            ['CAM-001', 'KLCC-Main', 'Kuala Lumpur City Centre, Main Entrance', 3.15776, 101.71156, 'online', '192.168.1.101', 80, 'mjpeg', '/mjpeg'],
            ['CAM-002', 'KLCC-Parking', 'KLCC Underground Parking, Level B1', 3.15785, 101.71195, 'online', '192.168.1.102', 80, 'mjpeg', '/mjpeg'],
            ['CAM-003', 'Bukit Bintang-01', 'Jalan Bukit Bintang, Near Pavilion KL', 3.14894, 101.71356, 'online', '192.168.2.101', 80, 'snapshot', '/snapshot.jpg'],
            ['CAM-004', 'Bukit Bintang-02', 'Jalan Sultan Ismail, Near Lot 10', 3.14778, 101.71234, 'online', '192.168.2.102', 80, 'mjpeg', '/mjpeg'],
            ['CAM-005', 'KL Sentral-01', 'KL Sentral Station, Main Concourse', 3.13398, 101.68685, 'online', '192.168.3.101', 554, 'rtsp', '/live'],
            ['CAM-006', 'KL Sentral-02', 'KL Sentral, Nu Sentral Mall Entrance', 3.13356, 101.68612, 'online', '192.168.3.102', 80, 'mjpeg', '/video.cgi'],
            ['CAM-007', 'Chow Kit-01', 'Jalan Chow Kit, Near Bazaar Baru', 3.16534, 101.69876, 'online', '192.168.4.101', 80, 'mjpeg', '/mjpeg'],
            ['CAM-008', 'Pudu-01', 'Jalan Pudu, Near Pudu Sentral', 3.13987, 101.71123, 'online', '192.168.5.101', 80, 'snapshot', '/snapshot.jpg'],
            ['CAM-009', 'Pudu-02', 'Jalan Pasar, Near Pudu Wet Market', 3.13245, 101.71567, 'online', '192.168.5.102', 8080, 'mjpeg', '/stream'],
            ['CAM-010', 'Masjid Jamek-01', 'Masjid Jamek LRT Station, Entrance A', 3.14945, 101.69656, 'offline', '192.168.6.101', 80, 'mjpeg', '/mjpeg'],
            ['CAM-011', 'KLIA-Arrival', 'KLIA Terminal 1, International Arrival Hall', 2.75567, 101.70456, 'maintenance', '192.168.7.101', 80, 'hls', '/live/stream.m3u8'],
            ['CAM-012', 'KLIA-Departure', 'KLIA Terminal 1, Departure Hall Level 5', 2.75612, 101.70501, 'online', '192.168.7.102', 80, 'mjpeg', '/mjpeg'],
            ['CAM-013', 'Petaling Street-01', 'Jalan Petaling, Chinatown Main Entrance', 3.14456, 101.69734, 'online', '192.168.8.101', 80, 'mjpeg', '/mjpeg'],
            ['CAM-014', 'Brickfields-01', 'Jalan Tun Sambanthan, Near KL Sentral Monorail', 3.13123, 101.68765, 'online', '192.168.9.101', 80, 'snapshot', '/snapshot.jpg'],
            ['CAM-015', 'Ampang Park-01', 'Jalan Ampang, Near Ampang Park LRT', 3.16123, 101.71890, 'online', '192.168.10.101', 554, 'rtsp', '/live'],
        ];

        foreach ($cameras as $c) {
            Camera::create([
                'camera_id' => $c[0],
                'name' => $c[1],
                'location' => $c[2],
                'latitude' => $c[3],
                'longitude' => $c[4],
                'status' => $c[5],
                'ip_address' => $c[6],
                'port' => $c[7],
                'stream_type' => $c[8],
                'stream_url' => $c[9],
                'username' => 'admin',
                'password' => 'camera123',
                'description' => "IP surveillance camera {$c[1]} located at {$c[2]}. 1080p resolution, facial recognition enabled.",
                'registered_by' => 1,
            ]);
        }
    }
}
