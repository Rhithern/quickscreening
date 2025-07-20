<?php
// includes/email.php
// Make sure you already have mail setup and/or PHPMailer or similar if needed

function sendInterviewCompletionNotification($candidateEmail, $positionName, $adminEmail) {
    $subjectAdmin = "Interview Completed: $positionName";
    $messageAdmin = "Candidate $candidateEmail has completed the interview for position: $positionName.";

    $subjectCandidate = "Thank you for completing your interview";
    $messageCandidate = "Dear Candidate,\n\nWe have received your interview for the position: $positionName.\nThank you for your time!\n\nBest Regards,\nQuickScreening Team";

    // Send to admin
    mail($adminEmail, $subjectAdmin, $messageAdmin);

    // Send confirmation to candidate
    mail($candidateEmail, $subjectCandidate, $messageCandidate);
}
?>

