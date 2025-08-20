import { computed } from 'vue';

// Office coordinates
export const officeCoordinates = { lat: 7.70496, lng: 100.00464 };

// Pickup locations
export const pickupLocations = [
    { lat: 7.830301, lng: 100.305199, name: 'โรงสีทิพย์พานิช' },
    { lat: 7.954374, lng: 100.214424, name: 'โรงสีจิตเจริญ' },
    { lat: 8.360839, lng: 100.144012, name: 'โรงสีวาสนา' },
    { lat: 7.857257, lng: 100.248482, name: 'โรงสีชูเชิด' },
    { lat: 7.950381, lng: 100.237488, name: 'โรงสีวัฒนพรอินเตอร์โกลเด้น' },
    { lat: 7.9543074, lng: 100.2137756, name: 'โรงสีโชคอำนวย' }
];

// Delivery points
export const deliveryLocations = [
    { lat: 7.729165, lng: 99.956551, name: 'ละอองฟาร์ม' },
    { lat: 7.757777, lng: 99.894615, name: 'ชวลิตรฟาร์ม' },
    { lat: 7.733764, lng: 100.078102, name: 'สมยศ สยมพร' },
    { lat: 7.601144, lng: 99.995308, name: 'พี่ถาวร' },
    { lat: 7.609554, lng: 100.000046, name: 'มิตรภาพ (บิ๊ก โชคสุข)' },
    { lat: 7.192664, lng: 100.215332, name: 'พี่ธีระ ป่าบอน' },
    { lat: 7.170626, lng: 99.780846, name: 'อิสรา แพะ ปะเหลียน' },
    { lat: 7.638457, lng: 99.991425, name: 'ลุงสิทธิ์' },
    { lat: 7.676242, lng: 99.966705, name: 'วิสาหกิจนาขยาด' },
    { lat: 7.669500, lng: 99.977710, name: 'สำราญ นาขยาด' },
    { lat: 7.660380, lng: 99.912090, name: 'เบียร์ตะแพน' },
    { lat: 7.619116, lng: 100.046215, name: 'พี่อมร: โค เขาเจียก' },
    { lat: 7.825231, lng: 99.858521, name: 'สารวัตรวิทย์ ป่ายอม' },
    { lat: 7.619197, lng: 99.995644, name: 'พี่กูน เลี้ยงหมู ลำพาย' },
    { lat: 7.851799, lng: 99.941856, name: 'อุดมพร ป่ายอม' },
    { lat: 7.617067, lng: 99.979172, name: 'ญาดา การเกษตร ฉลอง' },
    { lat: 7.384710, lng: 99.574310, name: 'ปัณณวัชร์ ตรัง' },
    { lat: 7.116088, lng: 100.251747, name: 'จุฑามาศฟาร์ม พี่ติ๋ม' },
    { lat: 7.233577, lng: 100.365112, name: 'พ่อให้ฟาร์ม พี่ติ๋ม' },
    { lat: 7.178449, lng: 100.319244, name: 'สิงห์เงินเจริญฟาร์ม พี่ติ๋ม' },
    { lat: 7.198642, lng: 100.321236, name: 'อุดมศิลป์ฟาร์ม นายกบัตร' },
    { lat: 7.741341, lng: 99.913490, name: 'ศรชัย' },
    { lat: 7.6961944, lng: 99.9683574, name: 'พี่นุ้ย ประสิทธิ์ ยุพา ฟาร์ม' },
    { lat: 7.1066048, lng: 100.3264869, name: 'เบทาโกร' }
];

// Calculate distance between two coordinates using Haversine formula
export const calculateDistance = (lat1: number, lng1: number, lat2: number, lng2: number): number => {
    const R = 6371000; // Earth's radius in meters
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLng/2) * Math.sin(dLng/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
};

// Composable for route filtering logic (office filtering removed)
export const useRouteFiltering = () => {
    return {
        calculateDistance,
        officeCoordinates,
        pickupLocations,
        deliveryLocations
    };
};