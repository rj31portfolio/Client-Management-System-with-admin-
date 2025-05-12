 
<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';
require_once '../vendor/autoload.php'; // Composer autoload for TCPDF

use TCPDF;

if (!isLoggedIn() || $_SESSION['role'] != 'client') {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];
$payment_id = $_GET['payment_id'] ?? 0;

$payment = $conn->query("SELECT p.*, pl.name, pl.price, u.username, u.email FROM payments p JOIN plans pl ON p.plan_id = pl.id JOIN users u ON p.user_id = u.id WHERE p.id = $payment_id AND p.user_id = $user_id")->fetch_assoc();

if (!$payment) {
    die("Invalid payment ID.");
}

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Client Management System');
$pdf->SetTitle('Invoice');
$pdf->SetSubject('Payment Invoice');
$pdf->SetKeywords('Invoice, Payment, Client Management System');

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 12);

// Invoice content
$html = '
<h1>Invoice</h1>
<p><strong>Client:</strong> ' . htmlspecialchars($payment['username']) . '</p>
<p><strong>Email:</strong> ' . htmlspecialchars($payment['email']) . '</p>
<p><strong>Plan:</strong> ' . htmlspecialchars($payment['name']) . '</p>
<p><strong>Amount:</strong> INR ' . htmlspecialchars($payment['amount']) . '</p>
<p><strong>Payment ID:</strong> ' . htmlspecialchars($payment['razorpay_payment_id']) . '</p>
<p><strong>Date:</strong> ' . htmlspecialchars($payment['created_at']) . '</p>
<p><strong>Status:</strong> ' . htmlspecialchars($payment['status']) . '</p>
';

// Write HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// Output PDF
$pdf->Output('invoice_' . $payment_id . '.pdf', 'D');

exit(); // TCPDF handles output, so exit here
?>