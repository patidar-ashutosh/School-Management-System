    </main>
    
    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>School Management System</h3>
                    <p>Empowering education through technology</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="/frontend/index.html">Home</a></li>
                        <li><a href="/frontend/admin/index.html">Principal Portal</a></li>
                        <li><a href="/frontend/user/index.html">Student Portal</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <p><i class="fas fa-envelope"></i> info@school.com</p>
                    <p><i class="fas fa-phone"></i> +1 234 567 8900</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> School Management System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="/assets/js/auth.js"></script>
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <script>
        // Logout function
        function logout() {
            fetch('/backend/controllers/auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ action: 'logout' })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '/frontend/index.html';
                } else {
                    alert('Logout failed: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Logout failed. Please try again.');
            });
        }
        
        // Toggle user menu
        document.addEventListener('DOMContentLoaded', function() {
            const userInfo = document.querySelector('.user-info');
            const dropdownMenu = document.querySelector('.dropdown-menu');
            
            if (userInfo && dropdownMenu) {
                userInfo.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('show');
                });
                
                document.addEventListener('click', function() {
                    dropdownMenu.classList.remove('show');
                });
            }
        });
    </script>
</body>
</html> 