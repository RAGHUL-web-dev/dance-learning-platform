    </main>
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-gray-500 text-sm">
                &copy; <?php echo date('Y'); ?> DanceLive Platform. All rights reserved. 
                Learn dance online with live interactive classes.
            </p>
        </div>
    </footer>
    
    <!-- Load scripts in correct order -->
    <script src="<?php echo BASE_PATH; ?>assets/js/main.js"></script>
    <script src="<?php echo BASE_PATH; ?>assets/js/webrtc.js"></script>
    
    <!-- Add a small script to verify loading -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Footer loaded');
        console.log('main.js loaded:', typeof window.showToast === 'function');
        console.log('webrtc.js loaded:', typeof window.initializeWebRTC === 'function');
    });
    </script>
</body>
</html>