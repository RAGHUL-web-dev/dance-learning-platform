<?php
session_start();
require_once 'includes/auth.php';
$auth->requireLogin();

$user = $auth->getCurrentUser();
require_once 'includes/subscription.php';

$subscription = new Subscription($conn, $user['id']);
$current_sub = $subscription->getCurrentSubscription();
$available_plans = Subscription::getAvailablePlans($conn);
$history = $subscription->getHistory();

// Handle subscription purchase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan_id'])) {
    $plan_id = $_POST['plan_id'];
    $payment_method = $_POST['payment_method'] ?? 'card';
    $transaction_id = 'TXN_' . uniqid() . '_' . bin2hex(random_bytes(4));
    
    $result = $subscription->subscribe($plan_id, $payment_method, $transaction_id);
    
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
        header("Location: " . BASE_PATH . "subscription.php");
        exit();
    } else {
        $error = $result['message'];
    }
}

// Handle cancellation
if (isset($_POST['cancel_subscription'])) {
    $result = $subscription->cancelSubscription();
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
        header("Location: " . BASE_PATH . "subscription.php");
        exit();
    } else {
        $error = $result['message'];
    }
}

require_once 'includes/header.php';
?>

<style>
.plan-card {
    background: linear-gradient(145deg, #1f2937, #111827);
    border: 1px solid #374151;
    border-radius: 1.5rem;
    padding: 2rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.plan-card:hover {
    transform: translateY(-8px);
    border-color: #3b82f6;
    box-shadow: 0 20px 40px -15px rgba(59, 130, 246, 0.5);
}

.plan-card.popular {
    border: 2px solid #3b82f6;
    transform: scale(1.05);
    z-index: 10;
}

.popular-badge {
    position: absolute;
    top: 1rem;
    right: -2rem;
    background: #3b82f6;
    color: white;
    padding: 0.5rem 3rem;
    transform: rotate(45deg);
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.plan-price {
    font-size: 3rem;
    font-weight: 700;
    color: white;
    margin: 1rem 0;
}

.plan-price small {
    font-size: 1rem;
    color: #9ca3af;
    font-weight: 400;
}

.feature-list {
    list-style: none;
    padding: 0;
    margin: 1.5rem 0;
}

.feature-list li {
    padding: 0.75rem 0;
    color: #d1d5db;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    border-bottom: 1px solid #374151;
}

.feature-list li:last-child {
    border-bottom: none;
}

.feature-list li svg {
    width: 1.25rem;
    height: 1.25rem;
    color: #10b981;
    flex-shrink: 0;
}

.current-badge {
    background-color: #10b981;
    color: white;
    padding: 0.25rem 1rem;
    border-radius: 9999px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-block;
}

.history-item {
    background-color: #1f2937;
    border: 1px solid #374151;
    border-radius: 0.75rem;
    padding: 1rem;
    margin-bottom: 0.75rem;
    transition: all 0.2s;
}

.history-item:hover {
    border-color: #4b5563;
}

.payment-methods {
    display: flex;
    gap: 1rem;
    margin: 1rem 0;
}

.payment-method {
    flex: 1;
    padding: 1rem;
    background-color: #374151;
    border: 1px solid #4b5563;
    border-radius: 0.75rem;
    text-align: center;
    color: #9ca3af;
    cursor: pointer;
    transition: all 0.2s;
}

.payment-method:hover {
    background-color: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

.payment-method.selected {
    background-color: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

.payment-method svg {
    width: 2rem;
    height: 2rem;
    margin-bottom: 0.5rem;
}
</style>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-white mb-4">Choose Your Plan</h1>
        <p class="text-xl text-gray-400 max-w-3xl mx-auto">
            Get unlimited access to live dance classes with our subscription plans
        </p>
    </div>

    <!-- Display messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="mb-6 bg-green-900/50 border border-green-700 text-green-200 px-4 py-3 rounded-lg">
            <?php echo $_SESSION['success_message']; ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="mb-6 bg-red-900/50 border border-red-700 text-red-200 px-4 py-3 rounded-lg">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <!-- Current Subscription Status -->
    <?php if ($current_sub): ?>
        <div class="mb-8 bg-gray-800 border border-gray-700 rounded-xl p-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-white mb-2">Current Subscription</h2>
                    <p class="text-gray-300">
                        <span class="font-medium text-blue-500"><?php echo $current_sub['plan_name']; ?></span> plan
                        • Active until <span class="font-medium"><?php echo date('F j, Y', strtotime($current_sub['end_date'])); ?></span>
                    </p>
                </div>
                <div class="flex gap-3">
                    <span class="current-badge">
                        <svg class="h-4 w-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Active
                    </span>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to cancel your subscription?');">
                        <button type="submit" name="cancel_subscription" class="text-sm text-red-500 hover:text-red-400">
                            Cancel Subscription
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Subscription Plans -->
    <div class="grid md:grid-cols-3 gap-8 mb-12">
        <?php foreach ($available_plans as $index => $plan): ?>
            <div class="plan-card <?php echo $plan['name'] === 'Monthly' ? 'popular' : ''; ?>">
                <?php if ($plan['name'] === 'Monthly'): ?>
                    <div class="popular-badge">Most Popular</div>
                <?php endif; ?>
                
                <h3 class="text-2xl font-bold text-white mb-2"><?php echo $plan['name']; ?></h3>
                <p class="text-gray-400 mb-4"><?php echo $plan['description']; ?></p>
                
                <div class="plan-price">
                    $<?php echo number_format($plan['price'], 2); ?>
                    <small>/<?php echo $plan['duration_days'] === 30 ? 'month' : 'year'; ?></small>
                </div>
                
                <ul class="feature-list">
                    <?php if ($plan['max_classes_per_day'] === null): ?>
                        <li>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Unlimited classes per day
                        </li>
                    <?php else: ?>
                        <li>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <?php echo $plan['max_classes_per_day']; ?> class per day
                        </li>
                    <?php endif; ?>
                    
                    <li>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <?php echo $plan['can_join_live'] ? 'Join live classes' : 'View only'; ?>
                    </li>
                    
                    <?php if ($user['role'] === 'instructor'): ?>
                        <li>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <?php echo $plan['can_create_classes'] ? 'Create unlimited classes' : 'Cannot create classes'; ?>
                        </li>
                    <?php endif; ?>
                    
                    <li>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <?php echo $plan['has_recording'] ? 'Class recordings included' : 'No recordings'; ?>
                    </li>
                    
                    <li>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <?php echo $plan['priority_support'] ? 'Priority support' : 'Standard support'; ?>
                    </li>
                    
                    <?php if (!empty($plan['features'])): ?>
                        <?php foreach ($plan['features'] as $feature => $value): ?>
                            <?php if (is_bool($value) && $value): ?>
                                <li>
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <?php echo ucwords(str_replace('_', ' ', $feature)); ?>
                                </li>
                            <?php elseif (is_numeric($value)): ?>
                                <li>
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <?php echo $value; ?> <?php echo ucwords(str_replace('_', ' ', $feature)); ?>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
                
                <?php if (!$current_sub || $current_sub['plan_name'] !== $plan['name']): ?>
                    <button onclick="showPaymentModal(<?php echo $plan['id']; ?>, '<?php echo $plan['name']; ?>', <?php echo $plan['price']; ?>)" 
                            class="w-full btn-primary py-3 text-center">
                        Choose Plan
                    </button>
                <?php else: ?>
                    <button class="w-full bg-gray-600 text-white py-3 rounded-lg font-medium cursor-not-allowed" disabled>
                        Current Plan
                    </button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3>Complete Your Subscription</h3>
                <span class="modal-close" onclick="closePaymentModal()">&times;</span>
            </div>
            
            <form method="POST" id="paymentForm">
                <div class="modal-body">
                    <div class="text-center mb-6">
                        <h4 id="selectedPlanName" class="text-xl font-bold text-white"></h4>
                        <p id="selectedPlanPrice" class="text-3xl font-bold text-blue-500 mt-2"></p>
                    </div>
                    
                    <div class="payment-methods">
                        <label class="payment-method selected" onclick="selectPaymentMethod('card', this)">
                            <input type="radio" name="payment_method" value="card" checked class="hidden">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            <div>Credit Card</div>
                        </label>
                        
                        <label class="payment-method" onclick="selectPaymentMethod('paypal', this)">
                            <input type="radio" name="payment_method" value="paypal" class="hidden">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4M4 8h16M4 16h8"></path>
                            </svg>
                            <div>PayPal</div>
                        </label>
                    </div>
                    
                    <!-- Card Details (simplified for demo) -->
                    <div class="mt-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Card Number</label>
                            <input type="text" placeholder="4242 4242 4242 4242" class="input-field" value="4242 4242 4242 4242">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Expiry</label>
                                <input type="text" placeholder="MM/YY" class="input-field" value="12/25">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">CVV</label>
                                <input type="text" placeholder="123" class="input-field" value="123">
                            </div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="plan_id" id="selectedPlanId">
                </div>
                
                <div class="modal-footer">
                    <button type="button" onclick="closePaymentModal()" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">Cancel</button>
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                        Pay Now
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Subscription History -->
    <?php if (!empty($history)): ?>
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-white mb-6">Subscription History</h2>
            <div class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden">
                <?php foreach ($history as $item): ?>
                    <div class="history-item">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-semibold text-white"><?php echo $item['plan_name']; ?> Plan</h4>
                                <p class="text-sm text-gray-400">
                                    Started: <?php echo date('M j, Y', strtotime($item['start_date'])); ?> • 
                                    Ended: <?php echo date('M j, Y', strtotime($item['end_date'])); ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="text-lg font-bold text-white">$<?php echo number_format($item['price'], 2); ?></span>
                                <p class="text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium
                                        <?php echo $item['status'] === 'active' ? 'bg-green-900/50 text-green-400' : 
                                                  ($item['status'] === 'expired' ? 'bg-gray-700 text-gray-300' : 
                                                   'bg-yellow-900/50 text-yellow-400'); ?>">
                                        <?php echo ucfirst($item['status']); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function showPaymentModal(planId, planName, planPrice) {
    document.getElementById('selectedPlanId').value = planId;
    document.getElementById('selectedPlanName').textContent = planName + ' Plan';
    document.getElementById('selectedPlanPrice').textContent = '$' + planPrice.toFixed(2);
    document.getElementById('paymentModal').style.display = 'block';
}

function closePaymentModal() {
    document.getElementById('paymentModal').style.display = 'none';
}

function selectPaymentMethod(method, element) {
    // Remove selected class from all payment methods
    document.querySelectorAll('.payment-method').forEach(el => {
        el.classList.remove('selected');
    });
    
    // Add selected class to clicked method
    element.classList.add('selected');
    
    // Check the radio button
    element.querySelector('input[type="radio"]').checked = true;
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('paymentModal');
    if (event.target === modal) {
        closePaymentModal();
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>