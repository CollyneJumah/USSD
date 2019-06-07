<?php
/**
 * Created by PhpStorm.
 * User: CollinsJumah
 * Date: 4/13/2019
 * Time: 18:57
 */


//connections required
require_once ('AfricasTalkingGateway.php');
require_once ('config.php');
require_once ('connection.php');

//receive the POSTs from AfricaStalking
$sessionId=$_POST['sessionId'];
$serviceCode=$_POST['serviceCode'];
$phoneNumber=$_POST['phoneNumber'];
$text=$_POST['text'];

//explored text to get valu of the latest interaction using textExplored function
$textArray=explode('*', $text);
$userResponse=trim(end($textArray));

//Set user level to zero(default level of user)
$level=0;

//check the level of user from the db and retain to zero if none is found
$sqlLv = "select level from session_levels where session_id ='".$sessionId." '";
$levelQuery = $conn->query($sqlLv);
if($resultLv = $levelQuery->fetch_assoc()) {
    $level = $resultLv['level'];
}


//=======================check if user/subscriber is in the database====================================================

$sqlCheckUser="SELECT * FROM parents WHERE phoneNumber LIKE '%".$phoneNumber."%' LIMIT 1";
//$resultsCheckUser=mysqli_query($conn,$sqlCheckUser);
//$userAvailability=mysqli_fetch_assoc($resultsCheckUser);
$userQuery=$conn->query($sqlCheckUser);
$userAvailability=$userQuery->fetch_assoc();


//if the user(parent) is available, serve the menu,, else prompt for registration

if($userAvailability && $userAvailability['name'] !=NULL && $userAvailability['admission'] !=NULL){
//    set level to zero
    if($level==0 || $level==1){
        switch ($userResponse){
            case "":
                if($level==0){
                    //             update user level and set to 1 and display the menu
                    $sqluserLvl="INSERT INTO `session_levels`(`session_id`,`phoneNumber`,`level`) VALUES('".$sessionId."','".$phoneNumber."',1)";
                    $resultsUserLvl=mysqli_query($conn,$sqluserLvl);
                    //   serve/ display the menu
                    $response = "CON Welcome to KERERI GIRLS " . $userAvailability['name'] . ". Choose a service.\n";
                    $response .= " 1. Register.\n";
                    $response .= " 2. Student details\n";
                    $response .= " 3. Exam Results\n";
                    $response .= " 4. Fee balance\n";
                    $response .= " 5. Fee Structure\n";
                    $response .= " 6. Subscribe for updates\n";
                    $response .= " 7. Exit";

                    header('Content-type: text/plain');
                    echo $response;
                }
                break;

            case "0":
                if($level==0){
//                    update user to the next level
                    $sqluserLvl1="INSERT INTO `session_levels`(`session_id`,`phoneNumber`,`level`) VALUES('".$sessionId."','".$phoneNumber."',1)";
                    $resultsUserLvl1=mysqli_query($conn,$sqluserLvl1);
                    //   serve/ display the menu
                    $response = "CON Welcome to KERERI GIRLS " . $userAvailability['name'] . ". Choose a service.\n";
                    $response .= " 1. Register.\n";
                    $response .= " 2. Student details\n";
                    $response .= " 3. Exam Results\n";
                    $response .= " 4. Fee balance\n";
                    $response .= " 5. Fee Structure\n";
                    $response .= " 6. Subscribe for updates\n";
                    $response .= " 7. Exit";

                    header('Content-type: text/plain');
                    echo $response;

                }
                break;

            case "1":
                if($level==1){
                    // Check if user's student is registered
                    $sqlReg="SELECT * FROM students WHERE phoneNumber LIKE '%".$phoneNumber."%' LIMIT 1 ";
                    $resultsReg=mysqli_query($conn,$sqlReg);
                    $checkReg=mysqli_num_rows($resultsReg);
                    if($checkReg>0){
                        $response = "CON You're already registered.\nPress 0 to main menu.";

                        $sqlLevelDemote = "UPDATE `session_levels` SET `level`=0 where `session_id`='" . $sessionId . "'";
                        $conn->query($sqlLevelDemote);

                    }
                    else{
                        $response = "END Your student data not matching or does not exist\n";
                    }

                    // Print the response onto the page so that our gateway can read it
                    header('Content-type: text/plain');
                    echo $response;
                }
                break;

            case "2":
                if($level==1){
//                    select students data from database and print out
                    $response = "END STUDENT INFORMATION:\n";
                    $sql3 = "SELECT * FROM students WHERE phoneNumber='$phoneNumber'";
                    $result3 = mysqli_query($conn, $sql3);
                    $checkData=mysqli_num_rows($result3);

                      while ($row3 = mysqli_fetch_array($result3)) {
                        $name3 = $row3['name'];
                        $admission3 = $row3['admission'];
                        $gender3 = $row3['gender'];
                        $form = $row3['form'];
                        $county = $row3['city'];

                     }
                     if($checkData==0){
                         $response = "END Student information for provided phone number does not exist.Please visit the institution to update data.\n";
                     }elseif ($checkData==1) {
                         //display student information
                         $response .= "KERERI GIRLS HIGH SCHOOL.\n";
                         $response .= "Name: " . $name3 . "\n";
                         $response .= "Admission: " . $admission3 . "\n";
                         $response .= "Gender: " . $gender3 . "\n";
                         $response .= "Class/Form:" . $form . "\n";
                         $response .= "County:" . $county . "\n";
                     }
                    // Print the response onto the page so that our gateway can read it
                    header('Content-type: text/plain');
                    echo $response;
                }
                break;

            case "3":
                if($level==1){
//                    ask user to select term ,, wanting to view results
                    $response = "CON  Please select the term you want to view results?\n";
                    $response .= "1. Term one\n";
                    $response .= "2. Term two\n";
                    $response .= "3. Term three\n";
                    $response .= "4. General\n";

//                    update session to another level
                    $sqlLvl3="UPDATE `session_levels` SET `level`=8 where `session_id`='".$sessionId."'";
                    $conn->query($sqlLvl3);
                    // Print the response onto the page so that our gateway can read it
                    header('Content-type: text/plain');
                    echo $response;
                }
                break;

            case "4":
                if($level==1) {
                    $nameF = $admissionF = $formF = $feePaid = $balance = $feeAmt = '';
                    //Select data from fee status table

                    $sqlFee = "SELECT * FROM feestatus WHERE phoneNumber LIKE '%" . $phoneNumber . "%' LIMIT 1";
                    $resultFee = mysqli_query($conn, $sqlFee);

                    //============check for availability in table feestatus=====================================
                    $checkUser = mysqli_num_rows($resultFee);
                    while ($rowFee = mysqli_fetch_array($resultFee)) {
                        $nameF = $rowFee['name'];
                        $admissionF = $rowFee['admission'];
                        $formF = $rowFee['form'];
                        $feeAmt = $rowFee['feeAmount'];
                        $feePaid = $rowFee['feePaid'];
                        $balance = $rowFee['balance'];

                    }
                    if ($checkUser == 0) {
                        $response = "END Student Fee information does not exist. Please wait for data to be updated.\nIf this's a new contact phone,Kindly make update to school database.";
//                        $response .= "Press 00 to main menu.";
//
//                        $sqlLevelDemote = "UPDATE `session_levels` SET `level`=00 where `session_id`='" . $sessionId . "'";
//                        $conn->query($sqlLevelDemote);

                    } elseif ($checkUser == 1) {
                        //display student information
                        $response = "END Student fee balance for the Year 2019 is:\n";
                        $response .= "Name:  " . $nameF . "\n";
                        $response .= "Admission:  " . $admissionF . "\n";
                        $response .= "Class/Form:  " . $formF . "\n";
                        $response .= "Fee Amount:  " . $feeAmt . "\n";
                        $response .= "Paid:  " . $feePaid . "\n";
                        $response .= "Balance:  " . $balance . "\n";
//                        $response .= "Press 00 to main menu.";
//
//                        $sqlLevelDemote = "UPDATE `session_levels` SET `level`=00 where `session_id`='" . $sessionId . "'";
//                        $conn->query($sqlLevelDemote);
                    }
                    header('Content-type: text/plain');
                    echo $response;
                }
                break;

            case "5":
                if($level==1){
//                    display fee structure for the school
                    $sqlFeeStr = "SELECT * FROM feestructure ORDER BY id DESC";
                    $resultFeeStr = mysqli_query($conn, $sqlFeeStr);

                    //============check for availability in table fee structures and display============//
                    $checkStr = mysqli_num_rows($resultFeeStr);
                    while ($rowFeeStr = mysqli_fetch_array($resultFeeStr)) {
                        $yearStr = $rowFeeStr['year'];
                        $termOne = $rowFeeStr['termOne'];
                        $trmTwo = $rowFeeStr['termTwo'];
                        $termThree = $rowFeeStr['termThree'];
                        $totalFee=$rowFeeStr['totalYear'];

                    }
                    //display student information
                    $response = "END KERERI GIRLS HIGH SCHOOL FEE STRUCTURE:\n";
                    $response .= "Academic Year:  " . $yearStr . "\n";
                    $response .= "Term One:  Ksh." . $termOne . "\n";
                    $response .= "Term Two:  Ksh." . $trmTwo . "\n";
                    $response .= "Term Three:  Ksh." . $termThree . "\n";
                    $response .= "Total Year:  Ksh." . $totalFee. "\n";
//                    $response .= "Press 00 to main menu.";
//
//                    $sqlLevelDemote = "UPDATE `session_levels` SET `level`=00 where `session_id`='" . $sessionId . "'";
//                    $conn->query($sqlLevelDemote);
                    header('Content-type: text/plain');
                    echo $response;
                }
                break;

            case "6":
                if($level==1){
                    $response = "CON You are about to subscribe to the following services\n";
                    $response .= " Opening dates\nClosing dates\nExam commencement\nExam release\nFee payment deadline\nStudent progress.\n";
                    $response .= "Are you sure that you want to proceed\n";
                    $response .= "1. YES\n";
                    $response .= "2. NO\n";

//                    update session level to 9 coz user is continuing to enter
                    $sqlLvl10="UPDATE `session_levels` SET `level`=9 where `session_id`='".$sessionId."'";
                    $conn->query($sqlLvl10);

                    header('Content-type: text/plain');
                    echo $response;
                }
                break;

            case "7":
                if($level==1){
                    $response = "END Thank you for registering with KERERI GIRLS School-Parent USSD System.\n";

                    // Print the response onto the page so that ussd gateway can read it
                    header('Content-type: text/plain');
                    echo $response;
                }
                break;

            default:
                if($level==1){
                    // Return user to Main Menu & Demote user's level
                    $response = "CON You have to choose a service.\n";
                    $response .= "Press 0 to go back.\n";
                    //demote
                    $sqlLevelDemote = "UPDATE `session_levels` SET `level`=0 where `session_id`='" . $sessionId . "'";
                    $conn->query($sqlLevelDemote);

                    // Print the response onto the page so that our gateway can read it
                    header('Content-type: text/plain');
                    echo $response;
                }

        }

    }
    else{
        switch ($level){
            case 8:
                switch ($userResponse){
                    case "1":
                      $sqlAd1="SELECT * FROM students WHERE phoneNumber LIKE '%".$phoneNumber."%' LIMIT 1";
                        $resultsAd1=mysqli_query($conn,$sqlAd1);
                        $resultsCheckSt=mysqli_num_rows($resultsAd1);
                        while ($rowAd1=mysqli_fetch_array($resultsAd1)){
                            $Adm1 = $rowAd1['admission'];
                            $phone=$rowAd1['phoneNumber'];
                        }
                        if($resultsCheckSt==0){
                            $response= "END Student data does not exist or match with your phone number\nContact system administrator for update.";
//                            $response .= "Press 00 to main menu.";
//
//                            $sqlLevelDemote = "UPDATE `session_levels` SET `level`=00 where `session_id`='" . $sessionId . "'";
//                            $conn->query($sqlLevelDemote);
                        }elseif($resultsCheckSt==1) {


                            $sqlResults = "SELECT * FROM results WHERE admission = '" . $Adm1 . "'";
                            $results1 = mysqli_query($conn, $sqlResults);
                            $Sname = $sAdmission = $eng = $kisw = $bio = $math = $chem = $total = $points = $grade = '';
                            while ($rowR = mysqli_fetch_array($results1)) {
                                $Sname = $rowR['student_name'];
                                $sAdmission = $rowR['admission'];
                                $eng = $rowR['eng'];
                                $kisw = $rowR['ksw'];
                                $math = $rowR['math'];
                                $chem = $rowR['chem'];
                                $bio = $rowR['bio'];
                                $total = $rowR['total'];
                                $points = $rowR['points'];
                                $grade = $rowR['grade'];

                            }
                            //display student information
                            $response = "END STUDENT RESULTS FOR TERM 1 ACADEMIC YEAR 2019: \n";
                            $response .= "Student Name:  " . $Sname . "\n";
                            $response .= "Admission No: " . $sAdmission . "\n";
                            $response .= "English: " . $eng . "\n";
                            $response .= "Kiswahili: " . $kisw . "\n";
                            $response .= "Maths: " . $math . "\n";
                            $response .= "Chemistry: " . $chem . "\n";
                            $response .= "Biology: " . $bio . "\n";
                            $response .= "Total Marks: " . $total . "\n";
                            $response .= "Total Points: " . $points . "\n";
                            $response .= "GRADE: " . $grade . "\n";
//                            $response .= "Press 00 to main menu.";
//
//                            $sqlLevelDemote = "UPDATE `session_levels` SET `level`=00 where `session_id`='" . $sessionId . "'";
//                            $conn->query($sqlLevelDemote);
                        }
                        // Print the response onto the page so that our gateway can read it
                        header('Content-type: text/plain');
                        echo $response;
                        break;

                    case "2":
//                        display term 2 results
                        $response = "CON Kindly wait for term two results to be uploaded\n";
//                        $response .= "Press 0 to main menu.";
//
//                        $sqlLevelDemote = "UPDATE `session_levels` SET `level`=0 where `session_id`='" . $sessionId . "'";
//                        $conn->query($sqlLevelDemote);
                        // Print the response onto the page so that our gateway can read it
                        header('Content-type: text/plain');
                        echo $response;
                        break;

                    case "3":
//                        display term three results
                        $sqlAd1="SELECT * FROM students WHERE phoneNumber LIKE '%".$phoneNumber."%' LIMIT 1";
                        $resultsAd1=mysqli_query($conn,$sqlAd1);
                        $resultsCheckSt=mysqli_num_rows($resultsAd1);
                        while ($rowAd1=mysqli_fetch_array($resultsAd1)){
                            $Adm1 = $rowAd1['admission'];
                            $phone=$rowAd1['phoneNumber'];
                        }
                        if($resultsCheckSt==0){
                            $response= "END Student data does not exist or match with your phone number\nContact system administrator for update.";
//                            $response .= "Press 00 to main menu.";
//
//                            $sqlLevelDemote = "UPDATE `session_levels` SET `level`=00 where `session_id`='" . $sessionId . "'";
//                            $conn->query($sqlLevelDemote);
                        }elseif($resultsCheckSt==1) {


                            $sqlResults = "SELECT * FROM results WHERE admission = '" . $Adm1 . "'";
                            $results1 = mysqli_query($conn, $sqlResults);
                            $Sname = $sAdmission = $eng = $kisw = $bio = $math = $chem = $total = $points = $grade = '';
                            while ($rowR = mysqli_fetch_array($results1)) {
                                $Sname = $rowR['student_name'];
                                $sAdmission = $rowR['admission'];
                                $eng = $rowR['eng'];
                                $kisw = $rowR['ksw'];
                                $math = $rowR['math'];
                                $chem = $rowR['chem'];
                                $bio = $rowR['bio'];
                                $total = $rowR['total'];
                                $points = $rowR['points'];
                                $grade = $rowR['grade'];

                            }
                            //display student information
                            $response = "END STUDENT RESULTS FOR TERM 3 ACADEMIC YEAR 2019: \n";
                            $response .= "Student Name:  " . $Sname . "\n";
                            $response .= "Admission No: " . $sAdmission . "\n";
                            $response .= "English: " . $eng . "\n";
                            $response .= "Kiswahili: " . $kisw . "\n";
                            $response .= "Maths: " . $math . "\n";
                            $response .= "Chemistry: " . $chem . "\n";
                            $response .= "Biology: " . $bio . "\n";
                            $response .= "Total Marks: " . $total . "\n";
                            $response .= "Total Points: " . $points . "\n";
                            $response .= "GRADE: " . $grade . "\n";
//                            $response .= "Press 00 to main menu.";
//
//                            $sqlLevelDemote = "UPDATE `session_levels` SET `level`=00 where `session_id`='" . $sessionId . "'";
//                            $conn->query($sqlLevelDemote);
                        }
                        // Print the response onto the page so that our gateway can read it
                        header('Content-type: text/plain');
                        echo $response;
                        break;
                    case "4":
//                        display current results
                        $sqlAd1="SELECT * FROM students WHERE phoneNumber LIKE '%".$phoneNumber."%' LIMIT 1";
                        $resultsAd1=mysqli_query($conn,$sqlAd1);
                        $resultsCheckSt=mysqli_num_rows($resultsAd1);
                        while ($rowAd1=mysqli_fetch_array($resultsAd1)){
                            $Adm1 = $rowAd1['admission'];
                            $phone=$rowAd1['phoneNumber'];
                        }
                        if($resultsCheckSt==0){
                            $response= "END Student data does not exist or match with your phone number\nContact system administrator for update.";
//                            $response .= "Press 00 to main menu.";
//
//                            $sqlLevelDemote = "UPDATE `session_levels` SET `level`=00 where `session_id`='" . $sessionId . "'";
//                            $conn->query($sqlLevelDemote);
                        }elseif($resultsCheckSt==1) {


                            $sqlResults = "SELECT * FROM results WHERE admission = '" . $Adm1 . "'";
                            $results1 = mysqli_query($conn, $sqlResults);
                            $Sname = $sAdmission = $eng = $kisw = $bio = $math = $chem = $total = $points = $grade = '';
                            while ($rowR = mysqli_fetch_array($results1)) {
                                $Sname = $rowR['student_name'];
                                $sAdmission = $rowR['admission'];
                                $eng = $rowR['eng'];
                                $kisw = $rowR['ksw'];
                                $math = $rowR['math'];
                                $chem = $rowR['chem'];
                                $bio = $rowR['bio'];
                                $total = $rowR['total'];
                                $points = $rowR['points'];
                                $grade = $rowR['grade'];

                            }
                            //display student information
                            $response = "END CURRENT AVERAGE STUDENT RESULTS FOR ACADEMIC YEAR 2019: \n";
                            $response .= "Student Name:  " . $Sname . "\n";
                            $response .= "Admission No: " . $sAdmission . "\n";
                            $response .= "English: " . $eng . "\n";
                            $response .= "Kiswahili: " . $kisw . "\n";
                            $response .= "Maths: " . $math . "\n";
                            $response .= "Chemistry: " . $chem . "\n";
                            $response .= "Biology: " . $bio . "\n";
                            $response .= "Total Marks: " . $total . "\n";
                            $response .= "Total Points: " . $points . "\n";
                            $response .= "GRADE: " . $grade . "\n";
//                            $response .= "Press 00 to main menu.";
//
//                            $sqlLevelDemote = "UPDATE `session_levels` SET `level`=00 where `session_id`='" . $sessionId . "'";
//                            $conn->query($sqlLevelDemote);
                        }
                        // Print the response onto the page so that our gateway can read it
                        header('Content-type: text/plain');
                        echo $response;
                        break;

                    case 9:
                        switch ($userResponse){
                            case "1":
                                $response="END Thank you for subscribing for KERERI GIRLS USSD services.";

                                header('Content-type: text/plain');
                                echo $response;
                                break;

                            case "2":
                                $response="END You just cancelled Our services. No worry We will still update you.";

                                header('Content-type: text/plain');
                                echo $response;
                                break;

                            default:
                                $response = "END Apologies, something went wrong. \n";
                                // Print the response onto the page so that ussd gateway can read it
                                header('Content-type: text/plain');
                                echo $response;
                                break;
                        }
                }
        }
    }


}else{
//    register user
//    check user response is not empty
    if($userResponse==""){
        switch ($level){
            case 0:
                //            update user to the next level so you dont serve them the same menu
                $sql10b = "INSERT INTO `session_levels`(`session_id`, `phoneNumber`,`level`) VALUES('".$sessionId."','".$phoneNumber."', 1)";
                $conn->query($sql10b);

                //Insert the phoneNumber, since it comes with the first POST
                $sql10c = "INSERT INTO parents(`phonenumber`) VALUES ('".$phoneNumber."')";
                $conn->query($sql10c);

                //Serve the menu request for name
                $response = "CON Please enter your Name";

                // Print the response onto the page so that our gateway can read it
                header('Content-type: text/plain');
                echo $response;
                break;

            case 1:
                // Request again for name - level has not changed...
                $response = "CON Name not supposed to be empty. Please enter your name \n";

                // Print the response onto the page so that gateway can read it
                header('Content-type: text/plain');
                echo $response;
                break;

            case 2:
                //10f. Request for city again --- level has not changed...
                $response = "CON Admission not supposed to be empty. Please reply with Student Admission number\n";

                // Print the response onto the page so that our gateway can read it
                header('Content-type: text/plain');
                echo $response;
                break;

            default:
                //10g. End the session
                $response = "END Apologies, something went wrong. \n";

                // Print the response onto the page so that our gateway can read it
                header('Content-type: text/plain');
                echo $response;
                break;

        }
    }else{
//        if not empty, update user details
        switch ($level) {
            case 0:
                //Graduate the user to the next level, so you dont serve them the same menu
                $sqlb = "INSERT INTO `session_levels`(`session_id`, `phoneNumber`,`level`) VALUES('".$sessionId."','".$phoneNumber."', 1)";
                $conn->query($sqlb);

                // Insert the phoneNumber, since it comes with the first POST
                $sqlc = "INSERT INTO parents (`phonenumber`) VALUES ('".$phoneNumber."')";
                $conn->query($sqlc);

                //10d. Serve the menu request for name
                $response = "CON Please enter your name";

                // Print the response onto the page so that our gateway can read it
                header('Content-type: text/plain');
                echo $response;
                break;
            case 1:
                //Update Name, Request for city
                $sql11b = "UPDATE parents SET `name`='".$userResponse."' WHERE `phonenumber` LIKE '%". $phoneNumber ."%'";
                $conn->query($sql11b);

                //graduate the user to the admission level
                $sql11c = "UPDATE `session_levels` SET `level`=2 WHERE `session_id`='".$sessionId."'";
                $conn->query($sql11c);

                //request for the admission
                $response = "CON Please enter your Student Admission";

                // Print the response onto the page so that our gateway can read it
                header('Content-type: text/plain');
                echo $response;
                break;
            case 2:
                //11d. Update city
                $sql11d = "UPDATE parents SET `admission`='".$userResponse."' WHERE `phonenumber` = '". $phoneNumber ."'";
                $conn->query($sql11d);

                //11e. Change level to 0
                $sql11e = "INSERT INTO `session_levels`(`session_id`,`phoneNumber`,`level`) VALUES('".$sessionId."','".$phoneNumber."',1)";
                $conn->query($sql11e);

                //11f. Serve the menu request for name
                $response = "END You have been successfully registered to KERERI GIRLS. Dial *384*62316# to choose a service.";

                // Print the response onto the page so that our gateway can read it
                header('Content-type: text/plain');
                echo $response;
                break;
            default:
                //11g. Request for city again
                $response = "END Apologies, something went wrong... \n";

                // Print the response onto the page so that our gateway can read it
                header('Content-type: text/plain');
                echo $response;
                break;
        }
    }

}
?>
