<?php
require_once("../db.php");
//預設模式為使用者意見發表統計
$mode = $_GET["mode"];


//使用者

//使用者共同資料
$users = sels("users");
foreach ($users as $user) {
    $datas[$user["id"]] = count(sels("opinion", ["user_id" => $user["id"]]));
}
arsort($datas); //保留key排序大到小
$thrdatas = array_slice($datas, 0, 3, true); //取前三高發表意見



if ($mode == "user_each_score") {
    // 1~5分的切換使用者
    if (isset($_POST["change_user"])) {
        $user_id = $_POST["select_user"];
        $mode = "user_each_score";
    } else {
        // 預設user_id
        reset($thrdatas); //先抓到第一個元素
        $user_id = key($thrdatas); //在抓那個元素的鍵名
    }
}
?>
<!-- 統計圖表 -->
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once("header.php"); ?>
</head>

<body>
    <?php require_once("../nav.php"); ?>
    <form action="" method="post">
        <div class="container">
            <h2>圖形統計</h2>
            <!-- <pre>
            使用者意見發表統計 (橫條圖)
            使用者評1~5分個別次數 (圓餅圖)
            各專案意見發表總量 (橫條圖)
            各專案的面向意見總計 例: 專案x 面向1有x個意見，面向2有x個意見 (圓餅圖)
            </pre> -->
            <hr>

            <!-- 選擇 -->
            <ul class="nav nav-tabs">
                <li class="active"><a href="#users" data-toggle="tab">使用者統計</a></li>
                <li><a href="#project" data-toggle="tab">專案統計</a></li>
            </ul>

            <!-- 內容 -->
            <div class="tab-content">
                <div class="tab-pane fade active in" id="users">
                    <h3>使用者統計
                        <a class="btn btn-warning" href="chart.php?mode=user_total">使用者意見發表統計</a>
                        <a class="btn btn-info" href="chart.php?mode=user_each_score">使用者評1~5分個別次數</a>
                    </h3>



                    <!-- 使用者評1~5分個別次數 -->
                    <?php if ($mode == "user_each_score") { ?>
                        <h4>使用者評1~5分個別次數
                            <select name="select_user" id="">
                                <?php foreach (array_keys($thrdatas) as $id) {
                                    $user = sel("users", ["id" => $id]);
                                ?>
                                    <option value="<?= $user["id"] ?>"><?= $user["name"] ?></option>
                                <?php } ?>
                            </select>
                            <button class="btn btn-inverse" name="change_user" type="submit">送出</button>
                        </h4>


                        <!-- 使用者意見發表統計 -->
                    <?php }
                    if ($mode == "user_total") {
                        $name_data = join(",", array_keys($thrdatas));

                        $opinion_count = join(",", array_values($thrdatas));
                    ?>
                        <h4>使用者意見發表統計</h4>

                    <?php } ?>
                    <div id="content<?= $mode ?>"></div>
                </div>
                <div class="tab-pane fade" id="project">
                    <h3>專案統計</h3>
                </div>

            </div>
            <script>
                <?php if ($mode == "user_each_score") {
                    $chart = "pie";
                ?>
                    var user_id = <?= $user_id ?>;
                    var chart_data = {
                        chart: {
                            type: "<?= $chart ?>"
                        },
                        title: {
                            text: "使用者評分次數"
                        },
                        series: [],
                        plotOptions: {
                            pie: {
                                allowPointerSelect: true,
                                cursor: "pointer",
                                dataLabels: {
                                    enables: true,
                                    format: "<b>{point.name}</b>{point.percentage:.1f}%"
                                }
                            }
                        }
                    };

                    
                    fetch("chart_data.php?mode=user_each_score&user_id=" + user_id)
                        .then(res => res.json())
                        .then(res => {

                            chart_data.series = [{
                                data: res,
                            }]
                            Highcharts.chart("content<?= $mode ?>", chart_data)
                        })
                <?php }
                if ($mode == "user_total") {
                    $chart = "bar";
                    foreach (explode(",", $name_data) as $id) { //名字      
                        $names[] = array_values(query("SELECT `users`.`name` FROM `users` WHERE `id` = $id"));
                    }
                ?>
                    var names = <?= json_encode($names) ?>;
                    var opinion_count = '<?= $opinion_count ?>';
                    get_test();

                    async function get_test() {
                        await fetch("chart_data.php?mode=user_total&opinion_count=" + opinion_count)
                            .then(res => res.json())
                            .then(res => {
                                console.log("in fetch")
                                for (var i = 0; i < res.length; i++) {
                                    redata = parseInt(res[i])
                                }
                                console.log(redata);
                                Highcharts.chart("content<?= $mode ?>", {
                                    chart: {
                                        type: "<?= $chart ?>"
                                    },
                                    title: {
                                        text: "使用者意見發表統計"
                                    },
                                    series: [{
                                        data: redata
                                    }],
                                    xAxis: {
                                        categories: names
                                    }
                                    // plotOptions: {
                                    //     pie: {
                                    //         allowPointerSelect: true,
                                    //         cursor: "pointer",
                                    //         dataLabels: {
                                    //             enables: true,
                                    //             format: "<b>{point.name}</b>{point.percentage:.1f}%"
                                    //         }
                                    //     }
                                    // }
                                })
                            })
                    }
                <?php } ?>
            </script>
        </div>
    </form>
</body>

</html>