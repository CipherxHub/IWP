<?php
// ============================================================
// Practical 9 - Online Quiz App using PHP + MySQL
// ============================================================
// SETUP INSTRUCTIONS:
// 1. Run this SQL in MySQL/phpMyAdmin:
//    CREATE DATABASE quiz_db;
//    USE quiz_db;
//    CREATE TABLE questions (
//        id INT AUTO_INCREMENT PRIMARY KEY,
//        question TEXT NOT NULL,
//        opt_a VARCHAR(255), opt_b VARCHAR(255),
//        opt_c VARCHAR(255), opt_d VARCHAR(255),
//        correct_ans CHAR(1) NOT NULL,
//        category VARCHAR(50) DEFAULT 'General'
//    );
//    INSERT INTO questions VALUES
//    (NULL,'What does HTML stand for?','Hyper Text Markup Language','High Tech Modern Language','Hyper Transfer Modern Language','Heavy Text Machine Language','A','Web'),
//    (NULL,'Which PHP function starts a session?','start_session()','session_start()','php_session()','begin_session()','B','PHP'),
//    (NULL,'Which is not a JavaScript data type?','String','Boolean','Float','Number','C','JavaScript'),
//    (NULL,'CSS stands for?','Cascading Style Sheets','Creative Style System','Computer Style Sheets','Colorful Style System','A','Web'),
//    (NULL,'Which tag creates a hyperlink in HTML?','<link>','<href>','<a>','<url>','C','Web');
//
// 2. Update DB credentials below.
// 3. Run: php -S localhost:8000
// ============================================================

session_start();

// ===== DATABASE CONNECTION =====
$host   = 'localhost';
$dbname = 'quiz_db';
$user   = 'root';
$pass   = '';   // Change to your MySQL password

$pdo = null;
$dbError = null;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $dbError = $e->getMessage();
}

// ===== LOAD QUESTIONS =====
$questions = [];
if ($pdo) {
    $stmt = $pdo->query("SELECT * FROM questions ORDER BY id");
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ===== HANDLE QUIZ SUBMISSION =====
$score      = null;
$answers    = [];
$totalQ     = count($questions);
$submitted  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    $submitted = true;
    $score = 0;

    foreach ($questions as $q) {
        $userAns = $_POST['q_' . $q['id']] ?? '';
        $answers[$q['id']] = [
            'user'    => strtoupper($userAns),
            'correct' => $q['correct_ans'],
            'right'   => strtoupper($userAns) === $q['correct_ans'],
        ];
        if (strtoupper($userAns) === $q['correct_ans']) {
            $score++;
        }
    }

    // Store score in session
    $_SESSION['last_score'] = $score;
    $_SESSION['last_total'] = $totalQ;
}

$percent = ($totalQ > 0 && $score !== null) ? round(($score / $totalQ) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <title>Practical 9 - Online Quiz (PHP + MySQL)</title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: #e8eaf6;
      min-height: 100vh;
      padding: 28px 20px;
      color: #333;
    }

    .header {
      background: linear-gradient(135deg, #4527a0, #5e35b1);
      color: white;
      border-radius: 14px 14px 0 0;
      padding: 22px 32px;
      max-width: 750px;
      margin: 0 auto;
    }

    .header h1 { font-size: 22px; }
    .header p  { font-size: 13px; color: #b39ddb; margin-top: 4px; }

    .container {
      background: white;
      border-radius: 0 0 14px 14px;
      padding: 32px;
      max-width: 750px;
      margin: 0 auto;
      box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    }

    /* DB Error */
    .db-error {
      background: #fff3e0;
      border: 2px solid #ff9800;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
      font-size: 14px;
    }

    .db-error h3 { color: #e65100; margin-bottom: 8px; }
    .db-error code {
      display: block;
      background: #fff8e1;
      padding: 8px 12px;
      border-radius: 6px;
      margin-top: 8px;
      font-size: 12px;
      color: #bf360c;
    }

    /* Score Banner */
    .score-banner {
      border-radius: 12px;
      padding: 24px;
      text-align: center;
      margin-bottom: 28px;
    }

    .score-banner.great  { background: #e8f5e9; border: 2px solid #43a047; }
    .score-banner.good   { background: #e3f2fd; border: 2px solid #1976d2; }
    .score-banner.fail   { background: #ffebee; border: 2px solid #e53935; }

    .score-big { font-size: 52px; font-weight: 900; }
    .score-banner.great .score-big  { color: #2e7d32; }
    .score-banner.good  .score-big  { color: #1565c0; }
    .score-banner.fail  .score-big  { color: #c62828; }

    .score-label { font-size: 15px; color: #666; margin-top: 6px; }

    /* Progress Bar */
    .progress-wrap { background: #e0e0e0; border-radius: 20px; height: 12px; overflow: hidden; margin: 12px 0 4px; }
    .progress-fill { height: 100%; border-radius: 20px; transition: width 1s ease; }

    /* Question Card */
    .q-card {
      border: 1.5px solid #e8eaf6;
      border-radius: 12px;
      padding: 20px 24px;
      margin-bottom: 18px;
      transition: border-color 0.2s;
    }

    .q-card.correct-card { border-color: #43a047; background: #f1fff3; }
    .q-card.wrong-card   { border-color: #e53935; background: #fff5f5; }

    .q-num {
      font-size: 11px;
      font-weight: 700;
      color: #9e9e9e;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 6px;
    }

    .q-text {
      font-size: 16px;
      font-weight: 600;
      color: #1a1a2e;
      margin-bottom: 16px;
      line-height: 1.5;
    }

    /* Options */
    .options { display: flex; flex-direction: column; gap: 8px; }

    .option-label {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 14px;
      border: 1.5px solid #e0e0e0;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      transition: all 0.15s;
    }

    .option-label:hover { background: #f5f5f5; border-color: #9e9e9e; }
    .option-label input[type="radio"] { accent-color: #4527a0; }

    /* After submit styles */
    .option-label.opt-correct { background: #e8f5e9; border-color: #43a047; color: #1b5e20; font-weight: 600; }
    .option-label.opt-wrong   { background: #ffebee; border-color: #e53935; color: #c62828; }

    .opt-key {
      font-weight: 700;
      font-size: 13px;
      color: #4527a0;
      min-width: 18px;
    }

    /* Submit Button */
    .submit-btn {
      width: 100%;
      background: linear-gradient(135deg, #4527a0, #5e35b1);
      color: white;
      border: none;
      border-radius: 12px;
      padding: 15px;
      font-size: 17px;
      font-weight: 700;
      cursor: pointer;
      margin-top: 10px;
      transition: opacity 0.2s;
    }

    .submit-btn:hover { opacity: 0.9; }

    .retry-btn {
      display: inline-block;
      background: #e8eaf6;
      color: #4527a0;
      border: 2px solid #4527a0;
      border-radius: 10px;
      padding: 10px 24px;
      font-size: 15px;
      font-weight: 700;
      text-decoration: none;
      margin-top: 12px;
    }
  </style>
</head>
<body>

<div class="header">
  <h1>📝 Online Quiz</h1>
  <p>Questions loaded from MySQL database via PHP</p>
</div>

<div class="container">

  <?php if ($dbError): ?>
    <div class="db-error">
      <h3>⚠️ Database Connection Failed</h3>
      <p>Please check your MySQL credentials and setup:</p>
      <code><?= htmlspecialchars($dbError) ?></code>
      <p style="margin-top:10px;font-size:13px;color:#555;">
        Run the SQL setup commands from the top of this file to create the database and table.
      </p>
    </div>
  <?php endif; ?>

  <?php if ($submitted && $score !== null): ?>
    <!-- SCORE DISPLAY -->
    <?php
      $cls = $percent >= 80 ? 'great' : ($percent >= 50 ? 'good' : 'fail');
      $msg = $percent >= 80 ? '🎉 Excellent!' : ($percent >= 50 ? '👍 Good Job!' : '📚 Keep Studying!');
    ?>
    <div class="score-banner <?= $cls ?>">
      <div class="score-big"><?= $score ?>/<?= $totalQ ?></div>
      <div class="score-label"><?= $msg ?> You scored <?= $percent ?>%</div>
      <div class="progress-wrap" style="max-width:300px;margin:12px auto 0;">
        <div class="progress-fill" style="width:<?= $percent ?>%; background:<?= $cls === 'great' ? '#43a047' : ($cls === 'good' ? '#1976d2' : '#e53935') ?>;"></div>
      </div>
    </div>
    <a class="retry-btn" href="?">🔄 Try Again</a>
    <br><br>
  <?php endif; ?>

  <?php if (!empty($questions)): ?>
    <form method="POST">
      <?php foreach ($questions as $idx => $q):
        $qid     = $q['id'];
        $userAns = $answers[$qid]['user']  ?? '';
        $corrAns = $answers[$qid]['correct'] ?? '';
        $isRight = $answers[$qid]['right']  ?? null;
        $cardCls = $submitted ? ($isRight ? 'correct-card' : 'wrong-card') : '';
      ?>
        <div class="q-card <?= $cardCls ?>">
          <div class="q-num">Question <?= $idx + 1 ?> / <?= $totalQ ?> &nbsp;·&nbsp; <?= htmlspecialchars($q['category']) ?></div>
          <div class="q-text"><?= htmlspecialchars($q['question']) ?></div>

          <div class="options">
            <?php foreach (['A', 'B', 'C', 'D'] as $opt):
              $optVal = $q['opt_' . strtolower($opt)];
              if (!$optVal) continue;

              // Determine CSS class after submission
              $optCls = '';
              if ($submitted) {
                if ($opt === $corrAns)            $optCls = 'opt-correct';
                elseif ($opt === $userAns)        $optCls = 'opt-wrong';
              }
            ?>
              <label class="option-label <?= $optCls ?>">
                <input type="radio" name="q_<?= $qid ?>" value="<?= $opt ?>"
                       <?= ($userAns === $opt) ? 'checked' : '' ?>
                       <?= $submitted ? 'disabled' : '' ?>
                />
                <span class="opt-key"><?= $opt ?>.</span>
                <?= htmlspecialchars($optVal) ?>
                <?php if ($submitted && $opt === $corrAns): ?> ✅<?php endif; ?>
                <?php if ($submitted && $opt === $userAns && $opt !== $corrAns): ?> ❌<?php endif; ?>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>

      <?php if (!$submitted): ?>
        <button type="submit" name="submit_quiz" class="submit-btn">🚀 Submit Quiz</button>
      <?php endif; ?>
    </form>

  <?php elseif (!$dbError): ?>
    <p style="color:#888;text-align:center;padding:30px;">No questions found. Please add questions to the database.</p>
  <?php endif; ?>

</div>
</body>
</html>
