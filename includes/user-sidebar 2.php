<aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">
        <!-- Home Item -->
        <li class="nav-item">
            <a class="nav-link" href="pages-home.php">
                <i class="bi bi-house-door"></i>
                <span>Home</span>
            </a>
        </li>

        <!-- Antibiotics Section -->
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-toggle="collapse" href="#antibiotics-nav" role="button">
                <i class="bi bi-capsule"></i><span>Antibiotics</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>

            <ul id="antibiotics-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                <li>
                    <a href="pages-release-antibiotic.php">
                        <i class="bi bi-circle"></i><span>Dispensing Antibiotics</span>
                    </a>
                </li>
                <li>
                    <a href="pages-release-details.php">
                        <i class="bi bi-circle"></i><span>Dispensing Details</span>
                    </a>
                </li>
                <li>
                    <a href="pages-antibiotic-details.php">
                        <i class="bi bi-circle"></i> <span>Antibiotic Details</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Wards Details Item -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="pages-wards-details.php">
                <i class="bi bi-building-fill-gear"></i>
                <span>Wards Details</span>
            </a>
        </li>

        <!-- Profile Item -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="user-profile.php">
                <i class="bi bi-person-circle"></i>
                <span>Profile</span>
            </a>
        </li>

        <!-- Log Out Item -->
        <li class="nav-item">
            <a class="nav-link collapsed" href="logout.php">
                <i class="bi bi-box-arrow-right"></i>
                <span>Log Out</span>
            </a>
        </li>
    </ul>
</aside>

<!-- Bootstrap JavaScript Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
