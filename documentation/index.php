<?php
session_start();
?>
<!doctype html>
<html class="no-js " lang="en">


<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="description" content="Medipulse Pharmacy Management System Documentation. This guide provides comprehensive instructions on how to effectively use and manage the various features within the Medipulse system. Whether you are an administrator, pharmacist, or staff, this documentation will help you navigate the platform and utilize its functionalities to enhance pharmacy operations.">
    <title>Medipulse Pharmacy Management System Documentation</title>
    <link rel="icon" href="logo-dark.ico" type="image/x-icon"> <!-- Favicon-->
    <link rel="stylesheet" href="assets/plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/plugins/jvectormap/jquery-jvectormap-2.0.3.min.css" />
    <link rel="stylesheet" href="assets/plugins/charts-c3/plugin.css" />

    <link rel="stylesheet" href="assets/plugins/morrisjs/morris.min.css" />
    <!-- Custom Css -->
    <link rel="stylesheet" href="assets/css/style.min.css">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">

    <style>
        html {
            scroll-behavior: smooth;
        }

        /* Back to Top Button Styles */
        #backToTopBtn {
            display: none;
            /* Hidden by default */
            position: fixed;
            /* Fixed position */
            bottom: 20px;
            /* Place the button at the bottom of the page */
            right: 30px;
            /* Place the button at the right of the page */
            z-index: 99;
            /* Make sure it does not overlap */
            border: none;
            /* Remove borders */
            outline: none;
            /* Remove outline */
            background-color: #007bff;
            /* Set a background color */
            color: white;
            /* Text color */
            cursor: pointer;
            /* Add a mouse pointer on hover */
            padding: 5px;
            /* Some padding */
            border-radius: 10px;
            /* Rounded corners */
            font-size: 14px;
            /* Increase font size */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            /* Add a subtle shadow */
            transition: background-color 0.3s, opacity 0.3s;
            /* Smooth transition for hover effects */
            height: 40px;
            /* Set a fixed height */
            width: 40px;
            /* Set a fixed width */
        }

        #backToTopBtn:hover {
            background-color: #0056b3;
            /* Darker background on hover */
            opacity: 0.9;
        }

        li.open a {
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>

<body class="theme-blush">

    <!-- Left Sidebar -->
    <aside id="leftsidebar" class="sidebar">
        <div class="navbar-brand">
            <button class="btn-menu ls-toggle-btn" type="button"><i class="zmdi zmdi-menu"></i></button>
        </div>
        <div class="menu">
            <ul class="list">
                <?php if (isset($_SESSION['role'])) { ?>
                    <?php if ($_SESSION['role'] == 'Superuser') { ?>
                        <li class="open"><a href="../dashboard.php"><i class="zmdi zmdi-home"></i><span>Back to Dashboard</span></a></li>
                    <?php } elseif ($_SESSION['role'] == 'Admin') { ?>
                        <li class="open"><a href="../dashboard.php"><i class="zmdi zmdi-home"></i><span>Back to Dashboard</span></a></li>
                    <?php } elseif ($_SESSION['role'] == 'Pharmacist') { ?>
                        <li class="open"><a href="../dashboard.php"><i class="zmdi zmdi-home"></i><span>Back to Dashboard</span></a></li>
                    <?php } elseif ($_SESSION['role'] == 'Assistant') { ?>
                        <li class="open"><a href="../dashboard.php"><i class="zmdi zmdi-home"></i><span>Back to Dashboard</span></a></li>
                    <?php } elseif ($_SESSION['role'] == 'Cashier') { ?>
                        <li class="open"><a href="../dashboard.php"><i class="zmdi zmdi-home"></i><span>Back to Dashboard</span></a></li>
                    <?php } ?>
                <?php } else { ?>
                    <li class="open"><a href="../index.php"><i class="zmdi zmdi-home"></i><span>Back to Login</span></a></li>
                <?php } ?>
                <li class="open"><a href="#system_overview"><i class="zmdi zmdi-info-outline"></i><span>System Overview</span></a></li>
                <li class="open"><a href="#user_roles"><i class="zmdi zmdi-accounts-alt"></i><span>User Roles and Permissions</span></a></li>
                        <li class="open"><a href="#admin_role"><i class="zmdi zmdi-account-box-mail"></i><span>Admin</span></a></li>
                        <li class="open"><a href="#pharmacist_role"><i class="zmdi zmdi-local-pharmacy"></i><span>Pharmacist</span></a></li>
                        <li class="open"><a href="#assistant_role"><i class="zmdi zmdi-account"></i><span>Assistant</span></a></li>
                        <li class="open"><a href="#cashier_role"><i class="zmdi zmdi-money-box"></i><span>Cashier</span></a></li>
                        <li class="open"><a href="#landing_page"><i class="zmdi zmdi-home"></i><span>Landing Page (index.php)</span></a></li>
                        <li class="open"><a href="#general_ui"><i class="zmdi zmdi-view-dashboard"></i><span>General UI and Navigation</span></a></li>
                        <li class="open"><a href="#important_notes"><i class="zmdi zmdi-alert-circle-o"></i><span>Important Notes</span></a></li>
            </ul>
        </div>
    </aside>


    <!-- Main Content -->

    <section class="content">
        <div class="">

            <!-- INTRODUCTION -->
            <div class="block-header">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2>MEDIPULSE DOCUMENTATION</h2>
                        <p class="lead mt-2">Welcome to the Medipulse Documentation. This guide provides comprehensive instructions on how to effectively use and manage the various features within the Medipulse Pharmacy Management System. Whether you are an administrator, pharmacist, or staff, this documentation will help you navigate the platform and utilize its functionalities to enhance pharmacy operations.</p>

                        <button class="btn btn-primary btn-icon mobile_menu d-lg-none d-md-none" type="button"><i class="zmdi zmdi-sort-amount-desc"></i></button>
                    </div>
                </div>
            </div>
            <!-- INTRODUCTION ENDS HERE -->

            <!-- SYSTEM OVERVIEW -->
            <div class="block-header" id="system_overview">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2>1. System Overview</h2>
                        <p class="lead mt-2">MediPulse is a comprehensive system designed to manage pharmacy operations, including inventory, sales, patient records, prescriptions, and user management. It aims to streamline daily tasks and provide insights into pharmacy performance.</p>
                    </div>
                </div>
            </div>
            <!-- SYSTEM OVERVIEW ENDS HERE -->

            <!-- USER ROLES AND PERMISSIONS -->
            <div class="block-header" id="user_roles">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2>2. User Roles and Permissions</h2>
                        <p class="lead mt-2">MediPulse defines several user roles, each with specific access levels and responsibilities to ensure secure and efficient operation.</p>
                    </div>
                </div>
            </div>
            <!-- USER ROLES AND PERMISSIONS ENDS HERE -->

           
            <!-- ADMIN ROLE -->
            <div class="block-header" id="admin_role">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h3>2.1. Admin</h3>
                        <p class="lead mt-2">The Admin role has extensive privileges, allowing them to manage most aspects of the pharmacy's operations and user accounts.</p>
                        <h4>Key Responsibilities & Features:</h4>
                        <ul>
                            <li><strong>Dashboard Access:</strong> View key performance indicators (KPIs) such as total profit, total sales count, total quantity sold, inventory sum, expected profit, total unique medicines, and total patients. Also view low stock, out of stock, expired, and soon-to-expire products, and recent sales.</li>
                            <li><strong>User Management:</strong>
                                <ul>
                                    <li><strong>Add New Users:</strong> Create new user accounts for Pharmacists, Assistants, and Cashiers.</li>
                                    <li><strong>Edit Existing Users:</strong> Modify usernames, passwords, and roles of existing users.</li>
                                    <li><strong>Delete Users:</strong> Remove user accounts.</li>
                                </ul>
                            </li>
                            <li><strong>Medicine Management:</strong>
                                <ul>
                                    <li><strong>Add New Medicines:</strong> Register new pharmaceutical products with details like name, description, quantity, cost price, selling price, batch number, and expiry date.</li>
                                    <li><strong>Edit Existing Medicines:</strong> Update details of existing medicines, including stock levels and pricing.</li>
                                    <li><strong>Delete Medicines:</strong> Remove medicine records from the system.</li>
                                </ul>
                            </li>
                            <li><strong>Patient Management:</strong>
                                <ul>
                                    <li><strong>Add New Patients:</strong> Register new patient records with personal and insurance details.</li>
                                    <li><strong>Edit Existing Patients:</strong> Update patient information.</li>
                                    <li><strong>Delete Patients:</strong> Remove patient records.</li>
                                </ul>
                            </li>
                            <li><strong>Prescription Management:</strong>
                                <ul>
                                    <li><strong>Add New Prescriptions:</strong> Create new prescription records, linking patients and medicines.</li>
                                    <li><strong>Edit Existing Prescriptions:</strong> Modify prescription details.</li>
                                    <li><strong>Delete Prescriptions:</strong> Remove prescription records.</li>
                                </ul>
                            </li>
                            <li><strong>Sales Management (Point of Sale - POS):</strong>
                                <ul>
                                    <li><strong>Add Items to Cart:</strong> Select medicines and specify quantities for sale.</li>
                                    <li><strong>Update Cart Items:</strong> Adjust quantities of items in the cart.</li>
                                    <li><strong>Remove Items from Cart:</strong> Delete items from the current sale.</li>
                                    <li><strong>Checkout:</strong> Complete sales transactions, update inventory, and generate receipts.</li>
                                    <li><strong>View Sales History:</strong> Access records of past sales.</li>
                                </ul>
                            </li>
                            <li><strong>Supplier Management:</strong> Manage supplier information.</li>
                            <li><strong>Purchase Order Management:</strong> Create and manage purchase orders for medicines.</li>
                            <li><strong>Logging:</strong> All actions performed by an Admin are logged for auditing purposes.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- ADMIN ROLE ENDS HERE -->

            <!-- PHARMACIST ROLE -->
            <div class="block-header" id="pharmacist_role">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h3>2.2. Pharmacist</h3>
                        <p class="lead mt-2">The Pharmacist role is primarily responsible for managing medicines, patient prescriptions, and assisting with sales.</p>
                        <h4>Key Responsibilities & Features:</h4>
                        <ul>
                            <li><strong>Dashboard Access:</strong> Likely has access to view key performance indicators (KPIs) and inventory alerts (low stock, out of stock, expired, soon-to-expire products).</li>
                            <li><strong>Medicine Management:</strong>
                                <ul>
                                    <li><strong>Add New Medicines:</strong> Register new pharmaceutical products.</li>
                                    <li><strong>Edit Existing Medicines:</strong> Update medicine details, especially stock and expiry dates.</li>
                                    <li><strong>Delete Medicines:</strong> Remove medicine records.</li>
                                </ul>
                            </li>
                            <li><strong>Patient Management:</strong>
                                <ul>
                                    <li><strong>Add New Patients:</strong> Register new patient records.</li>
                                    <li><strong>Edit Existing Patients:</strong> Update patient information.</li>
                                    <li><strong>Delete Patients:</strong> Remove patient records.</li>
                                </ul>
                            </li>
                            <li><strong>Prescription Management:</strong>
                                <ul>
                                    <li><strong>Add New Prescriptions:</strong> Create new prescription records.</li>
                                    <li><strong>Edit Existing Prescriptions:</strong> Modify prescription details.</li>
                                    <li><strong>Delete Prescriptions:</strong> Remove prescription records.</li>
                                </ul>
                            </li>
                            <li><strong>Sales Management (Point of Sale - POS):</strong>
                                <ul>
                                    <li><strong>Add Items to Cart:</strong> Select medicines and specify quantities for sale.</li>
                                    <li><strong>Update Cart Items:</strong> Adjust quantities of items in the cart.</li>
                                    <li><strong>Remove Items from Cart:</strong> Delete items from the current sale.</li>
                                    <li><strong>Checkout:</strong> Complete sales transactions, update inventory, and generate receipts.</li>
                                    <li><strong>View Sales History:</strong> Access records of past sales.</li>
                                </ul>
                            </li>
                            <li><strong>Logging:</strong> All actions performed by a Pharmacist are logged.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- PHARMACIST ROLE ENDS HERE -->

            <!-- ASSISTANT ROLE -->
            <div class="block-header" id="assistant_role">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h3>2.3. Assistant</h3>
                        <p class="lead mt-2">The Assistant role supports the pharmacy operations, focusing on patient and prescription management, and potentially sales.</p>
                        <h4>Key Responsibilities & Features:</h4>
                        <ul>
                            <li><strong>Dashboard Access:</strong> May have limited access to view general dashboard information, but not sensitive financial data.</li>
                            <li><strong>Patient Management:</strong>
                                <ul>
                                    <li><strong>Add New Patients:</strong> Register new patient records.</li>
                                    <li><strong>Edit Existing Patients:</strong> Update patient information.</li>
                                    <li><strong>Delete Patients:</strong> Remove patient records.</li>
                                </ul>
                            </li>
                            <li><strong>Prescription Management:</strong>
                                <ul>
                                    <li><strong>Add New Prescriptions:</strong> Create new prescription records.</li>
                                    <li><strong>Edit Existing Prescriptions:</strong> Modify prescription details.</li>
                                    <li><strong>Delete Prescriptions:</strong> Remove prescription records.</li>
                                </ul>
                            </li>
                            <li><strong>Sales Management (Point of Sale - POS):</strong>
                                <ul>
                                    <li><strong>Add Items to Cart:</strong> Select medicines and specify quantities for sale.</li>
                                    <li><strong>Update Cart Items:</strong> Adjust quantities of items in the cart.</li>
                                    <li><strong>Remove Items from Cart:</strong> Delete items from the current sale.</li>
                                    <li><strong>Checkout:</strong> Complete sales transactions, update inventory, and generate receipts.</li>
                                </ul>
                            </li>
                            <li><strong>Logging:</strong> All actions performed by an Assistant are logged.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- ASSISTANT ROLE ENDS HERE -->

            <!-- CASHIER ROLE -->
            <div class="block-header" id="cashier_role">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h3>2.4. Cashier</h3>
                        <p class="lead mt-2">The Cashier role is primarily focused on handling sales transactions.</p>
                        <h4>Key Responsibilities & Features:</h4>
                        <ul>
                            <li><strong>Dashboard Access:</strong> May have limited access to view general dashboard information, primarily related to sales.</li>
                            <li><strong>Sales Management (Point of Sale - POS):</strong>
                                <ul>
                                    <li><strong>Add Items to Cart:</strong> Select medicines and specify quantities for sale.</li>
                                    <li><strong>Update Cart Items:</strong> Adjust quantities of items in the cart.</li>
                                    <li><strong>Remove Items from Cart:</strong> Delete items from the current sale.</li>
                                    <li><strong>Checkout:</strong> Complete sales transactions, update inventory, and generate receipts.</li>
                                </ul>
                            </li>
                            <li><strong>Logging:</strong> All actions performed by a Cashier are logged.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- CASHIER ROLE ENDS HERE -->

            <!-- LANDING PAGE (INDEX.PHP) -->
            <div class="block-header" id="landing_page">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2>3. Landing Page</h2>
                        <p class="lead mt-2">After successful login, users are redirected to the landing page, which serves as a general welcome and landing page. This page displays the user's current role and provides a prompt to select a section from the navigation sidebar.</p>
                        <h4>Key Features:</h4>
                        <ul>
                            <li><strong>Role Display:</strong> Clearly shows the logged-in user's role (e.g., Superuser, Admin, Pharmacist).</li>
                            <li><strong>Navigation Prompt:</strong> Guides users to utilize the sidebar for accessing different modules and functionalities based on their permissions.</li>
                            <li><strong>Sidebar Integration:</strong> The sidebar is dynamically loaded, presenting navigation options tailored to the user's role.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- LANDING PAGE (INDEX.PHP) ENDS HERE -->

            <!-- GENERAL UI AND NAVIGATION -->
            <div class="block-header" id="general_ui">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2>4. General User Interface and Navigation</h2>
                        <p class="lead mt-2">The MediPulse system features a user-friendly interface with a sidebar for navigation and a main content area for displaying information and forms.</p>
                        <ul>
                            <li><strong>Sidebar:</strong> Provides quick access to different modules like Dashboard, Users, Medicines, Patients, Prescriptions, Sales, Sales History, Purchase Orders, Suppliers, and Logs. The visible options in the sidebar will vary based on the user's role.</li>
                            <li><strong>Navbar:</strong> Contains user-specific information, notifications, and logout options.</li>
                            <li><strong>Forms:</strong> Used for adding and editing records (e.g., users, medicines, patients, prescriptions).</li>
                            <li><strong>Tables:</strong> Display lists of records with options for editing and deleting.</li>
                            <li><strong>Search Functionality:</strong> Available in sales for searching medicines and patients.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- GENERAL UI AND NAVIGATION ENDS HERE -->

            <!-- IMPORTANT NOTES -->
            <div class="block-header" id="important_notes">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2>4. Important Notes</h2>
                        <ul>
                            <li><strong>Session Management:</strong> The system uses secure session management to protect user data and maintain login status.</li>
                            <li><strong>Action Logging:</strong> All significant user actions are logged for auditing and accountability.</li>
                            <li><strong>Database Interaction:</strong> The system interacts with a structured database to store and retrieve all operational data.</li>
                            <li><strong>Receipt Generation:</strong> Upon successful checkout, a receipt can be generated.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- IMPORTANT NOTES ENDS HERE -->

    </section>


    <!-- Jquery Core Js -->
    <script src="assets/bundles/libscripts.bundle.js"></script> <!-- Lib Scripts Plugin Js ( jquery.v3.2.1, Bootstrap4 js) -->
    <script src="assets/bundles/vendorscripts.bundle.js"></script> <!-- slimscroll, waves Scripts Plugin Js -->

    <script src="assets/bundles/jvectormap.bundle.js"></script> <!-- JVectorMap Plugin Js -->
    <script src="assets/bundles/sparkline.bundle.js"></script> <!-- Sparkline Plugin Js -->
    <script src="assets/bundles/c3.bundle.js"></script>

    <script src="assets/bundles/mainscripts.bundle.js"></script>
    <script src="assets/js/pages/index.js"></script>
    <script src="assets/bootstrap/js/bootstrap.bundle.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('#leftsidebar .menu .list a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();

                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);

                    if (targetElement) {
                        // Find the parent accordion-collapse element
                        const accordionCollapse = targetElement.closest('.accordion-collapse');
                        if (accordionCollapse) {
                            const bsCollapse = new bootstrap.Collapse(accordionCollapse, {
                                toggle: false
                            });
                            bsCollapse.show();

                            // Scroll to the target element after a short delay to allow accordion to open
                            setTimeout(() => {
                                targetElement.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'start'
                                });
                            }, 300); // Adjust delay as needed
                        } else {
                            // If not inside an accordion, just scroll
                            targetElement.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });
                        }
                    }
                });
            });

            // Back to Top Button functionality
            var mybutton = document.getElementById("backToTopBtn");

            // When the user scrolls down 20px from the top of the document, show the button
            window.onscroll = function() {
                scrollFunction()
            };

            function scrollFunction() {
                if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                    mybutton.style.display = "block";
                } else {
                    mybutton.style.display = "none";
                }
            }

            // When the user clicks on the button, scroll to the top of the document
            mybutton.addEventListener('click', function() {
                document.body.scrollTop = 0; // For Safari
                document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
            });
        });
    </script>
    <button onclick="topFunction()" id="backToTopBtn" title="Go to top" class="btn btn-primary btn-sm btn-circle"><i class="zmdi zmdi-chevron-up"></i></button>
</body>


</html>
