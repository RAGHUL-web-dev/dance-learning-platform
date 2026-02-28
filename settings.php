<?php
session_start();
require_once 'includes/auth.php';
$auth->requireLogin();

$user = $auth->getCurrentUser();

require_once 'includes/header.php';
?>

<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-white mb-8">Account Settings</h1>
    
    <div class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden">
        <!-- Notification Settings -->
        <div class="p-6 border-b border-gray-700">
            <h2 class="text-xl font-semibold text-white mb-4">Notifications</h2>
            
            <div class="space-y-4">
                <label class="flex items-center justify-between">
                    <span class="text-gray-300">Email notifications for new classes</span>
                    <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600 bg-gray-700 border-gray-600 rounded">
                </label>
                
                <label class="flex items-center justify-between">
                    <span class="text-gray-300">Push notifications for live classes</span>
                    <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600 bg-gray-700 border-gray-600 rounded" checked>
                </label>
                
                <label class="flex items-center justify-between">
                    <span class="text-gray-300">Weekly newsletter</span>
                    <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600 bg-gray-700 border-gray-600 rounded" checked>
                </label>
            </div>
        </div>
        
        <!-- Privacy Settings -->
        <div class="p-6 border-b border-gray-700">
            <h2 class="text-xl font-semibold text-white mb-4">Privacy</h2>
            
            <div class="space-y-4">
                <label class="flex items-center justify-between">
                    <span class="text-gray-300">Make profile public</span>
                    <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600 bg-gray-700 border-gray-600 rounded">
                </label>
                
                <label class="flex items-center justify-between">
                    <span class="text-gray-300">Show activity status</span>
                    <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600 bg-gray-700 border-gray-600 rounded" checked>
                </label>
            </div>
        </div>
        
        <!-- Save Button -->
        <div class="p-6 bg-gray-900/50">
            <button class="btn-primary px-8">Save Settings</button>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>