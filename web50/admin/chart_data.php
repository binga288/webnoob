<?php
require_once("../db.php");
// 使用者意見發表統計 統計資料
// user_total
if ($_GET["mode"] == "user_total") {
    if (isset($_GET["opinion_count"])) { //總意見
        echo json_encode(array_map('intval', explode(',', $_GET["opinion_count"])));
        // foreach(explode(',', $_GET["opinion_count"]) as $count){
        //     echo json_encode(intval($count));
        // }
    }
    if (isset($_GET["name_data"])) {
        foreach (explode(",", $_GET["name_data"]) as $id) { //名字      
            $names[] = implode(array_values(query("SELECT `users`.`name` FROM `users` WHERE `id` = $id")));
        }
        echo json_encode($names);
    }
}
// 使用者評1~5分個別次數 統計資料
if ($_GET["mode"] == "user_each_score") {
    for ($i = 1; $i <= 5; $i++) {
        for ($inside = 0; $inside < 2; $inside++) {
            $opinion_scores_count[0] = "第" . $i . "次評分";
            $opinion_scores_count[1] = count(sels("opinion_score", ["score" => $i, "user_id" => $_GET["user_id"]]));
        }
        $count[] = $opinion_scores_count;
    }
    echo json_encode($count);
}
