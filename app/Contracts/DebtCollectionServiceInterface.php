<?php

// app/Contracts/DebtCollectionServiceInterface.php
namespace App\Contracts;

interface DebtCollectionServiceInterface
{
    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(): array;

    /**
     * Get customers with filters and pagination
     */
    public function getCustomersWithFilters(array $filters): array;

    /**
     * Log a customer call
     */
    public function logCustomerCall(int $customerId, array $data): array;

    /**
     * Update customer tracking information
     */
    public function updateCustomerTracking(int $customerId, array $data): \App\Models\DebtCollectionTracking;

    /**
     * Get customer call history
     */
    public function getCustomerCallHistory(int $customerId): array;

    /**
     * Perform bulk actions on customers
     */
    public function performBulkAction(string $action, array $customerIds, array $options = []): array;

    /**
     * Get analytics data
     */
    public function getAnalyticsData(): array;

    /**
     * Export debt collection data
     */
    public function exportData(array $filters);
}