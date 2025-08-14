import type { Driver, Vehicle } from '@/types/delivery-notes';

interface NotesTemplateParams {
    drivers: Driver[];
    vehicles: Vehicle[];
    driverId?: string | number;
    vehicleId?: string | number;
    existingNotes?: string;
}

export function generateNotesTemplate({
    drivers,
    vehicles,
    driverId,
    vehicleId,
    existingNotes = ''
}: NotesTemplateParams): string {
    // Get selected driver and vehicle info
    const selectedDriver = driverId ? drivers.find(d => d.id == driverId) : null;
    const selectedVehicle = vehicleId ? vehicles.find(v => v.id == vehicleId) : null;
    
    const template = `PO 320045688
ทะเบียน ${selectedVehicle ? selectedVehicle.license_plate : '81-1041'}
${selectedVehicle?.province ? selectedVehicle.province : '81-1049'}
${selectedDriver ? selectedDriver.name : 'ชื่อ นามสกุล'}
เลขบัตรประชาชน ${selectedDriver?.id_card_number || '0000000000000'}
โทร. ${selectedDriver?.phone || '093-xxxxxxxx'}`;
    
    // If notes already exists, append template with a separator
    if (existingNotes && existingNotes.trim()) {
        return existingNotes + '\n\n' + template;
    } else {
        return template;
    }
}