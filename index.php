<?php
require_once 'scripts/class_scripts/db-connection.class.php';
$db = new DBconnection();
$conn = $db->connect();  // Assuming you're using the mysqli connection
if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
//$conn = new mysqli("localhost", "username", "password", "database_name");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT f.rating, f.comment, u.username 
        FROM feedback f 
        JOIN users u ON f.user_id = u.id 
        WHERE f.comment != '' 
        ORDER BY f.rating DESC, f.created_at DESC 
        LIMIT 3";

$result = $conn->query($sql);

$testimonials = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $testimonials[] = $row;
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multilingual Storytelling Chatbot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <?php require_once(__DIR__ . '/resources/nav.php'); ?>

    <!-- Hero Section -->
    <section id="hero" class="d-flex align-items-center justify-content-center">
        <div class="container text-center">
            <h1 class="display-4">Explore Stories in Your Language</h1>
            <p class="lead">Engage with our multilingual storytelling chatbot.</p>
            <a href="signin.php" class="btn btn-secondary btn-lg">Get Started</a>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 text-center ">
                    <i class="bi bi-chat-dots fs-1 text-primary"></i>
                    <h3 class="mt-3">Interactive Stories</h3>
                    <p>Enjoy interactive storytelling experiences tailored to your preferences.</p>
                </div>
                <div class="col-md-4 text-center ">
                    <i class="bi bi-translate fs-1 text-primary"></i>
                    <h3 class="mt-3">Multilingual Support</h3>
                    <p>Switch between languages effortlessly while enjoying captivating stories.</p>
                </div>
                <div class="col-md-4 text-center ">
                    <i class="bi bi-stars fs-1 text-primary"></i>
                    <h3 class="mt-3">Personalized Content</h3>
                    <p>Get stories recommended based on your interests and reading habits.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-5 " style="background-color: black;">
        <div class="container">
            <h2 class="text-center mb-4" style="color: white;">How It Works</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="card p-3">
                        <h4>1. Choose Your Language</h4>
                        <p>Select from multiple languages to experience stories in your preferred language.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3">
                        <h4>2. Pick a Story Genre</h4>
                        <p>Explore a variety of genres, from adventure to mystery, and pick the one that interests you the most.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3">
                        <h4>3. Start Your Journey</h4>
                        <p>Engage with the chatbot as it guides you through a unique and interactive storytelling experience.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Language Options Section -->
    <section id="languages" class="bg-light py-5">
        <div class="container text-center">
            <h2 class="mb-4">Supported Languages</h2>
            <div class="row">
                <div class="col-md-3 col-4">
                    <img src="images/eng_flag.png" alt="English" class="img-fluid rounded-circle mb-2">
                    <p>English</p>
                </div>
                <div class="col-md-3 col-4">
                    <img src="images/spanish_flag.jpg" alt="Spanish" class="img-fluid rounded-circle mb-2">
                    <p>Spanish</p>
                </div>
                <div class="col-md-3 col-4">
                    <img src="images/french_flag.jpg" alt="French" class="img-fluid rounded-circle mb-2">
                    <p>French</p>
                </div>
                <div class="col-md-3 col-4">
                    <img src="images/chinese_flag.png" alt="Chinese" class="img-fluid rounded-circle mb-2">
                    <p>Chinese</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">What Our Users Say</h2>
            <div class="row">
                <?php foreach ($testimonials as $testimonial): ?>
                    <div class="col-md-4">
                        <div class="card p-4">
                            <p>"<?php echo htmlspecialchars($testimonial['comment']); ?>"</p>
                            <h5 class="text-end">- <?php echo htmlspecialchars($testimonial['username']); ?></h5>
                            <div class="text-warning">
                                <?php
                                for ($i = 0; $i < 5; $i++) {
                                    if ($i < $testimonial['rating']) {
                                        echo '★';
                                    } else {
                                        echo '☆';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (count($testimonials) < 3): ?>
                    <?php for ($i = count($testimonials); $i < 3; $i++): ?>
                        <div class="col-md-4">
                            <div class="card p-4">
                                <p>"This chatbot has made storytelling so much more fun and engaging. I love how it adapts to my language and preferences!"</p>
                                <h5 class="text-end">- User</h5>
                                <div class="text-warning">
                                    ★★★★☆
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                <?php endif; ?>
                <div class="text-center">
                    <button id="reviews_btn" style="width: 20%; border-radius: 5px; margin: 10px; padding: 10px;">See more reviews</button>
                </div>
            </div>
        </div>
    </section>
   <!--  <section id="testimonials" class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">What Our Users Say</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="card p-4">
                        <p>"This chatbot has made storytelling so much more fun and engaging. I love how it adapts to my language and preferences!"</p>
                        <h5 class="text-end">- Sarah K.</h5>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-4">
                        <p>"The multilingual feature is a game-changer. My kids and I can enjoy stories together in different languages."</p>
                        <h5 class="text-end">- Tobi M.</h5>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-4">
                        <p>"A fantastic tool for learning languages through stories. The interactive element keeps me hooked."</p>
                        <h5 class="text-end">- Lisa P.</h5>
                    </div>
                </div>
            </div>
        </div>
    </section> -->

    <!-- Call to Action Section -->
    <section id="cta" class="py-5 text-white" style="background-color: #343a40;">
        <div class="container text-center">
            <h2>Ready to Start Your Adventure?</h2>
            <p>Join our community and dive into a world of stories.</p>
            <a href="signup.php" class="btn btn-outline-light btn-lg">Join Now</a>
        </div>
    </section>

    <!-- Contact Us Section -->
    <!-- <section id="contact" class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">Contact Us</h2>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <form>
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section> -->

    <footer class="text-center py-4">
        <p>&copy; 2024 StoryBot. All rights reserved.</p>
    </footer>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $('#reviews_btn').click(function() {
    console.log("reviews");
    window.location.href = 'all_feedbacks.php';
});
    </script>
</body>
</html>
