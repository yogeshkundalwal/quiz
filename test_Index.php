<?php 
$db = new PDO("mysql:host=localhost;dbname=quiz","root","");
$stmt = $db->prepare("SELECT * FROM questions");
/*$stmt->execute();*/

?>
<!DOCTYPE html>
<html>


<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    
    <title>PHP Quiz</title>
    
    <link rel="stylesheet" type="text/css" href="css/style.css" />
</head>

<body>
    <?php
    if($_POST['code'] == null && $_POST['question-1-answers'] == null) { ?>
        <center>
        <form action="Index.php" method="POST">
            enter your exam code: <input type="number" name="code">
          
        </form>
        </center>







    <?php } elseif( $_POST['question-1-answers'] == null ) {

/*    $sql = "SELECT * FROM questions WHERE code =".$_POST['code'];
    $stmt = $db->prepare($sql);*/
    $stmt = $db->prepare("SELECT * FROM questions WHERE code =".$_POST['code'] ); 
    $stmt->execute();
    ?>

    <div id="page-wrap">

        <h1>Quiz for students</h1>
        
        <form action="grade.php" method="post" id="quiz">

        
            <ol>
                <?php $code = $_POST['code']; ?>
                <!-- <input name="code" value="<?php $code; ?>" /> -->
                <?php
                $a = 0;

                while($row = $stmt->fetch()) {
                    $a++;
                ?>

                       
                <li>
                
                    <h3><?php echo $row['questins']; ?></h3>
                    
                    <div>
                        <input type="radio" name="question-<?php echo $a; ?>-answers" value="A" />
                        <label><?php echo $row['option A']; ?></label>
                    </div>
                    
                    <div>
                        <input type="radio" name="question-<?php echo $a; ?>-answers" value="B" />
                        <label><?php echo $row['option B']; ?></label>
                    </div>
                    
                    <div>
                        <input type="radio" name="question-<?php echo $a; ?>-answers" value="C" />
                        <label><?php echo $row['option C']; ?></label>
                    </div>
                    
                    <div>
                        <input type="radio" name="question-<?php echo $a; ?>-answers" value="D" />
                        <label><?php echo $row['option D']; ?></label>
                    </div>
                    <input name="code" value="<?php echo $code; ?>" hidden />
                </li>
            <?php } ?>
            
            </ol>
            
            <input class="" type="submit"/>
        
        </form>
    
    </div>
    <?php }
    else { 
        $stmt = $db->prepare("SELECT * FROM questions WHERE code =".$_POST['code'] ); 
        $stmt->execute();
        $answer = [];
        echo 'ram ram';
/*        $answer1 = $_POST['question-1-answers'];
        $answer2 = $_POST['question-2-answers'];*/
        $b = 1;
        echo 'ram ram';
/*        $count = $db->prepare('SELECT COUNT(code) FROM `questions` WHERE code ='.$POST['code']);
        $count->execute();
        $cc = $count->fetchColumn();
        echo $cc;*/
        while($row = $stmt -> fetch()) {
            $answer[] = $_POST['question-"'+$b+'"-answers'];
            $b++;
        }
        echo $answer[1];
        $ram = 'question-'+$b+'ram';
        echo 'ram ram';
        echo $ram;

        $totalCorrect = 0;
        
        if ($answer[1] == "B") { $totalCorrect++; }
        if ($answer[2] == "A") { $totalCorrect++; }
        /*if ($answer5) { $totalCorrect++; }*/
        
        echo "<div id='results'>$totalCorrect / 5 correct</div>";

        
    } ?>
    
    <script type="text/javascript">
    var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
    document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
    </script>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script type="text/javascript">
    var pageTracker = _gat._getTracker("UA-68528-29");
    pageTracker._initData();
    pageTracker._trackPageview();
    $(document).ready(function() {
        $('.btn').click(function() {
            var data = new FormData();
            data.append("action", "submit-response");
            data.append("value", 10);
            $.ajax({
                type: "POST",
                url: "handler.php",
                data:data,
                contentType:false,
                dataType:false,
                processData: false,
                success: function(response) {
                    console.log(response);
                },
                error: function() {
                    alert("error");
                }
            });
        });
    });
    </script>

</body>

</html>