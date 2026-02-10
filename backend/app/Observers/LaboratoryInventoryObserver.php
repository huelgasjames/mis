<?php

namespace App\Observers;

use App\Models\LaboratoryInventory;
use App\Models\AuditLog;
use App\Models\MaintenanceRecord;
use App\Models\RepairHistory;
use Illuminate\Support\Facades\Log;

class LaboratoryInventoryObserver
{
    public function creating(LaboratoryInventory $inventory): void
    {
        // Generate UUID if not present
        if (!$inventory->uuid) {
            $inventory->uuid = \Illuminate\Support\Str::uuid();
        }

        // Auto-set deployment date
        if ($inventory->status === 'Deployed' && !$inventory->deployment_date) {
            $inventory->deployment_date = now();
        }

        Log::info('Creating laboratory inventory', [
            'asset_tag' => $inventory->asset_tag,
            'computer_name' => $inventory->computer_name,
        ]);
    }

    public function created(LaboratoryInventory $inventory): void
    {
        // Log creation
        AuditLog::logChange($inventory, 'created', null, $inventory->toArray(), [
            'notes' => 'New laboratory inventory item created'
        ]);

        // Create initial maintenance record if needed
        if ($inventory->last_maintenance) {
            $inventory->maintenanceRecords()->create([
                'maintenance_type' => 'Initial Setup',
                'description' => 'Initial maintenance record',
                'performed_by' => 'System',
                'maintenance_date' => $inventory->last_maintenance,
                'next_maintenance_date' => $inventory->last_maintenance->addMonths(6),
            ]);
        }

        Log::info('Laboratory inventory created successfully', [
            'uuid' => $inventory->uuid,
            'asset_tag' => $inventory->asset_tag,
        ]);
    }

    public function updating(LaboratoryInventory $inventory): void
    {
        // Auto-set deployment date when changing to Deployed status
        if ($inventory->status === 'Deployed' && 
            !$inventory->getOriginal('deployment_date') && 
            !$inventory->deployment_date) {
            $inventory->deployment_date = now();
        }

        // Validate status transitions
        $oldStatus = $inventory->getOriginal('status');
        $newStatus = $inventory->status;

        if ($oldStatus !== $newStatus) {
            $this->validateStatusTransition($oldStatus, $newStatus);
        }

        Log::info('Updating laboratory inventory', [
            'uuid' => $inventory->uuid,
            'asset_tag' => $inventory->asset_tag,
            'changes' => $inventory->getDirty(),
        ]);
    }

    public function updated(LaboratoryInventory $inventory): void
    {
        $changes = $inventory->getDirty();
        
        // Log status changes
        if ($inventory->wasChanged('status')) {
            $this->handleStatusChange($inventory);
        }

        // Log condition changes
        if ($inventory->wasChanged('condition')) {
            AuditLog::logChange($inventory, 'condition_changed',
                $inventory->getOriginal('condition'),
                $inventory->condition,
                ['notes' => 'Condition changed from ' . $inventory->getOriginal('condition') . ' to ' . $inventory->condition]
            );
        }

        // Log assignment changes
        if ($inventory->wasChanged('assigned_to')) {
            AuditLog::logChange($inventory, 'assignment_changed',
                $inventory->getOriginal('assigned_to'),
                $inventory->assigned_to,
                ['notes' => 'Assignment changed']
            );
        }

        // Create repair history when starting repair
        if ($inventory->wasChanged('status') && $inventory->status === 'Under Repair') {
            $this->createRepairHistory($inventory);
        }

        // Update maintenance record when maintenance is performed
        if ($inventory->wasChanged('last_maintenance') && $inventory->last_maintenance) {
            $this->updateMaintenanceRecord($inventory);
        }

        Log::info('Laboratory inventory updated successfully', [
            'uuid' => $inventory->uuid,
            'changes' => array_keys($changes),
        ]);
    }

    public function deleting(LaboratoryInventory $inventory): void
    {
        // Check if inventory can be deleted
        if ($inventory->status === 'Deployed') {
            throw new \Exception('Cannot delete deployed inventory item. Recall it first.');
        }

        Log::warning('Deleting laboratory inventory', [
            'uuid' => $inventory->uuid,
            'asset_tag' => $inventory->asset_tag,
        ]);
    }

    public function deleted(LaboratoryInventory $inventory): void
    {
        // Log deletion
        AuditLog::logChange($inventory, 'deleted', $inventory->toArray(), null, [
            'notes' => 'Laboratory inventory item deleted'
        ]);

        Log::info('Laboratory inventory deleted successfully', [
            'uuid' => $inventory->uuid,
        ]);
    }

    public function restored(LaboratoryInventory $inventory): void
    {
        // Log restoration
        AuditLog::logChange($inventory, 'restored', null, $inventory->toArray(), [
            'notes' => 'Laboratory inventory item restored'
        ]);

        Log::info('Laboratory inventory restored', [
            'uuid' => $inventory->uuid,
        ]);
    }

    private function validateStatusTransition(string $from, string $to): void
    {
        $validTransitions = [
            'Available' => ['Deployed', 'Under Repair', 'Defective', 'For Disposal'],
            'Deployed' => ['Available', 'Under Repair', 'Defective'],
            'Under Repair' => ['Available', 'Deployed', 'Defective'],
            'Defective' => ['Under Repair', 'For Disposal'],
            'For Disposal' => [], // Terminal state
        ];

        if (!in_array($to, $validTransitions[$from] ?? [])) {
            throw new \Exception("Invalid status transition from {$from} to {$to}");
        }
    }

    private function handleStatusChange(LaboratoryInventory $inventory): void
    {
        $oldStatus = $inventory->getOriginal('status');
        $newStatus = $inventory->status;

        $notes = "Status changed from {$oldStatus} to {$newStatus}";
        
        // Add specific notes for certain transitions
        if ($oldStatus === 'Under Repair' && in_array($newStatus, ['Available', 'Deployed'])) {
            $notes .= ' - Repair completed';
        } elseif ($newStatus === 'Under Repair') {
            $notes .= ' - Repair started';
        } elseif ($newStatus === 'Deployed') {
            $notes .= ' - Item deployed';
        }

        AuditLog::logChange($inventory, 'status_changed', $oldStatus, $newStatus, [
            'notes' => $notes
        ]);
    }

    private function createRepairHistory(LaboratoryInventory $inventory): void
    {
        $inventory->repairHistory()->create([
            'repair_type' => 'Maintenance Repair',
            'issue_description' => $inventory->repair_description ?? 'General repair',
            'technician' => $inventory->repaired_by ?? 'Unknown',
            'start_date' => $inventory->repair_start_date ?? now(),
            'status' => 'in_progress',
        ]);
    }

    private function updateMaintenanceRecord(LaboratoryInventory $inventory): void
    {
        $inventory->maintenanceRecords()->create([
            'maintenance_type' => 'Scheduled Maintenance',
            'description' => 'Regular maintenance performed',
            'performed_by' => $inventory->repaired_by ?? 'System',
            'maintenance_date' => $inventory->last_maintenance,
            'next_maintenance_date' => $inventory->last_maintenance->addMonths(6),
            'cost' => 0,
        ]);
    }
}
