<?php
if (isset($_POST['names']) && isset($_POST['date'])) {
    // Load font and image paths
    $font = "Roboto-Medium.ttf"; // Use bold font for name
    $font_date = "Roboto-Regular.ttf";
    $image_path = "certificate.jpg";
    $output_dir = "certificate/";
    
    // Create the output directory if it doesn't exist
    if (!is_dir($output_dir)) {
        mkdir($output_dir, 0777, true);
    }
    
    // Get the date input and format it
    $input_date = $_POST['date'];
    $formatted_date = date("d M Y", strtotime($input_date));

    // Split the input into an array of names
    $names = explode("\n", trim($_POST['names']));
    $generated_files = [];
    
    // Iterate over each name
    foreach ($names as $name) {
        $name = trim($name); // Trim whitespace
        if (!empty($name)) {
            // Create image from the certificate template
            $image = imagecreatefromjpeg($image_path);
            
            // Define colors
            $color = imagecolorallocate($image, 8, 76, 122);
            $date_color = imagecolorallocate($image, 37, 54, 78);
            
            // Calculate the position to center the name
            $font_size = 50;
            $angle = 0;
            $image_width = imagesx($image);
            $bbox = imagettfbbox($font_size, $angle, $font, $name);
            $text_width = $bbox[2] - $bbox[0];
            $x = 800 + (600 - $text_width) / 2;
            $y = 500; // Y position for the name
            
            // Add name to the certificate using bold font
            imagettftext($image, $font_size, 0, $x, $y, $color, $font, $name);
            
            // Add date to the certificate
            imagettftext($image, 23, 0, 980, 995, $date_color, $font_date, $formatted_date);
            
            // Generate a unique filename
            // $file = time() . '_' . preg_replace('/\s+/', '_', strtolower($name)) . ".jpg";
            $file = "360digitmg_" . preg_replace('/\s+/', '_', strtolower($name)) . ".jpg";
            $file_path = $output_dir . $file;
            
            // Save the certificate image
            imagejpeg($image, $file_path);
            $generated_files[] = $file_path; // Store the generated file path
            
            // Destroy the image to free up memory
            imagedestroy($image);
        }
    }
    
    // Create a zip file
    $zip = new ZipArchive();
    $zip_file = $output_dir . 'certificates_' . time() . '.zip';
    
    if ($zip->open($zip_file, ZipArchive::CREATE) === TRUE) {
        foreach ($generated_files as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Generate Certificates</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <form method="post">
        <textarea name="names" placeholder="Enter names, one per line..."></textarea><br>
        <input type="date" name="date" required><br>
        <input type="submit" value="Generate Certificates">
    </form>

    <?php if (!empty($generated_files)): ?>
        <h3>Certificates Generated Successfully!</h3>
        <div class="certificates">
            <?php foreach ($generated_files as $file): ?>
                <div>
                    <img src="<?php echo $file; ?>" alt="Certificate" class="certificate-img">
                    <br><?php echo basename($file); ?>
                </div>
            <?php endforeach; ?>
        </div>
        <a href="download.php?file=<?php echo urlencode($zip_file); ?>" class="download-button">Download All Certificates</a>
    <?php endif; ?>

    <!-- Lightbox Modal -->
    <div id="lightbox" class="lightbox">
        <span class="close" onclick="closeLightbox()">&times;</span>
        <div class="lightbox-content">
            <img id="lightbox-img" src="" alt="Certificate">
        </div>
    </div>

    <script>
        // JavaScript to handle lightbox functionality
        const images = document.querySelectorAll('.certificate-img');
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = document.getElementById('lightbox-img');

        images.forEach(image => {
            image.addEventListener('click', () => {
                lightboxImg.src = image.src;
                lightbox.style.display = 'block';
            });
        });

        function closeLightbox() {
            lightbox.style.display = 'none';
        }
    </script>
</body>
</html>
