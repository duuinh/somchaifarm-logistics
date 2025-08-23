import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export interface Vehicle {
  id: number
  license_plate: string
  province: string | null
  vehicle_type: string | null
  load_capacity: number | null
  gps_device_id: number | null
  gps_provider: string | null
  is_active: boolean
  color: string | null
  tracking_name?: string
  formatted_name?: string
  created_at?: string
  updated_at?: string
}

export const useVehicleStore = defineStore('vehicle', () => {
  // State
  const vehicles = ref<Vehicle[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Getters
  const activeVehicles = computed(() => 
    vehicles.value.filter(v => v.is_active)
  )

  const vehiclesWithGps = computed(() =>
    activeVehicles.value.filter(v => v.gps_device_id !== null)
  )

  const vehiclesByType = computed(() => {
    const grouped: Record<string, Vehicle[]> = {}
    activeVehicles.value.forEach(vehicle => {
      const type = vehicle.vehicle_type || 'อื่นๆ'
      if (!grouped[type]) {
        grouped[type] = []
      }
      grouped[type].push(vehicle)
    })
    return grouped
  })

  const getVehicleById = computed(() => (id: number) =>
    vehicles.value.find(v => v.id === id)
  )

  const getVehicleColor = computed(() => (deviceId: number) => {
    const vehicle = vehicles.value.find(v => v.id === deviceId || v.gps_device_id === deviceId)
    return vehicle?.color || '#0000FF'
  })

  const getVehicleByGpsId = computed(() => (gpsDeviceId: number) =>
    vehicles.value.find(v => v.gps_device_id === gpsDeviceId)
  )

  // Actions
  const fetchVehicles = async () => {
    loading.value = true
    error.value = null
    
    try {
      const response = await fetch('/api/vehicles/with-gps')
      if (!response.ok) {
        throw new Error(`Failed to fetch vehicles: ${response.status}`)
      }
      const data = await response.json()
      vehicles.value = data
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to fetch vehicles'
      throw err
    } finally {
      loading.value = false
    }
  }

  const addVehicle = (vehicle: Vehicle) => {
    vehicles.value.push(vehicle)
  }

  const updateVehicle = (updatedVehicle: Vehicle) => {
    const index = vehicles.value.findIndex(v => v.id === updatedVehicle.id)
    if (index !== -1) {
      vehicles.value[index] = updatedVehicle
    }
  }

  const removeVehicle = (id: number) => {
    const index = vehicles.value.findIndex(v => v.id === id)
    if (index !== -1) {
      vehicles.value.splice(index, 1)
    }
  }

  const setVehicles = (newVehicles: Vehicle[]) => {
    vehicles.value = newVehicles
  }

  // Initialize store with optional initial data
  const initialize = async (initialData?: Vehicle[]) => {
    if (initialData && initialData.length > 0) {
      loading.value = false
      vehicles.value = initialData
    } else if (vehicles.value.length === 0) {
      await fetchVehicles()
    }
  }

  return {
    // State
    vehicles,
    loading,
    error,
    
    // Getters
    activeVehicles,
    vehiclesWithGps,
    vehiclesByType,
    getVehicleById,
    getVehicleColor,
    getVehicleByGpsId,
    
    // Actions
    fetchVehicles,
    addVehicle,
    updateVehicle,
    removeVehicle,
    setVehicles,
    initialize
  }
})