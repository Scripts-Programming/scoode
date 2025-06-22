<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // הגדר את כתובת המייל שאליה יישלחו הפרטים
    $to = "8582511c@gmail.com"; // **שנה את זה לכתובת המייל שלך!**
    $subject = "הרשמה חדשה לקייטנה דיגיטלית";

    // קבל את הנתונים מהטופס
    $fullName = htmlspecialchars($_POST['fullName']);
    $dob = htmlspecialchars($_POST['dob']);
    $parentName = htmlspecialchars($_POST['parentName']);
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['phone']);
    $grade = htmlspecialchars($_POST['grade']);
    $interests = htmlspecialchars($_POST['interests']);
    $terms = isset($_POST['terms']) ? 'כן' : 'לא';
    $newsletter = isset($_POST['newsletter']) ? 'כן' : 'לא';

    // בנה את גוף הודעת המייל
    $message = "פרטי הרשמה חדשים:\n\n";
    $message .= "שם מלא של הילד/ה: " . $fullName . "\n";
    $message .= "תאריך לידה: " . $dob . "\n";
    $message .= "שם מלא של ההורה/אפוטרופוס: " . $parentName . "\n";
    $message .= "כתובת אימייל (של ההורה): " . $email . "\n";
    $message .= "מספר טלפון (של ההורה): " . $phone . "\n";
    $message .= "כיתה עולה: " . $grade . "\n";
    $message .= "תחומי עניין דיגיטליים: " . $interests . "\n";
    $message .= "אישור תנאי שימוש: " . $terms . "\n";
    $message .= "רוצה לקבל עדכונים: " . $newsletter . "\n";

    // כותרות המייל
    $headers = "From: webmaster@yourdomain.com" . "\r\n" . // שנה את זה לכתובת מייל שקיימת בדומיין שלך
               "Reply-To: " . $email . "\r\n" .
               "X-Mailer: PHP/" . phpversion();

    // שלח את המייל
    if (mail($to, $subject, $message, $headers)) {
        echo "<h2>תודה רבה! הרשמתך התקבלה בהצלחה.</h2>";
        echo "<p>פרטי ההרשמה נשלחו אלינו וניצור קשר בקרוב.</p>";
        // אפשר גם להפנות את המשתמש לדף תודה:
        // header("Location: thank_you.html");
        // exit();
    } else {
        echo "<h2>אירעה שגיאה בשליחת ההרשמה.</h2>";
        echo "<p>אנא נסה/י שוב מאוחר יותר או צור/צרי קשר ישירות.</p>";
    }
} else {
    echo "<h2>גישה לא חוקית.</h2>";
    echo "<p>אנא מלא/י את הטופס כדי לשלוח את הפרטים.</p>";
}
?>
