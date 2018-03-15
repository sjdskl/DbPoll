<?php
/**
 * Created by PhpStorm.
 * User: kailishen
 * Date: 2018/3/15
 * Time: 下午3:16
 */

namespace DbPool\Library;


class TableRender
{

    public function render($data)
    {
        return $this->getTemplate($data);
    }

    public function getTemplate($data)
    {
        $t =<<<EOF
<html> 
<head> 
<title></title> 
<style type="text/css"> 
.table 
{ 
width: 100%; 
padding: 0; 
margin: 0; 
} 
th { 
font: bold 12px "Trebuchet MS", Verdana, Arial, Helvetica, sans-serif; 
color: #4f6b72; 
border-right: 1px solid #C1DAD7; 
border-bottom: 1px solid #C1DAD7; 
border-top: 1px solid #C1DAD7; 
letter-spacing: 2px; 
text-transform: uppercase; 
text-align: left; 
padding: 6px 6px 6px 12px; 
background: #CAE8EA no-repeat; 
} 
td { 
border-right: 1px solid #C1DAD7; 
border-bottom: 1px solid #C1DAD7; 
background: #fff; 
font-size:14px; 
padding: 6px 6px 6px 12px; 
color: #4f6b72; 
} 
td.alt { 
background: #F5FAFA; 
color: #797268; 
} 
th.spec,td.spec { 
border-left: 1px solid #C1DAD7; 
} 
/*---------for IE 5.x bug*/ 
html>body td{ font-size:14px;} 
tr.select th,tr.select td 
{ 
background-color:#CAE8EA; 
color: #797268; 
} 
</style> 
</head> 
<body> 
<table class="table" cellspacing="0" summary="The technical specifications of the Apple PowerMac G5 series"> 
<tr> 
<th class="spec">线程池</th> 
<th>works数量</th> 
<th>已使用work数量</th> 
<th>最后使用位置</th> 
</tr> 

EOF;
        foreach($data as $key => $row) {
            $row = "<tr> 
            <td class=\"spec\">{$key}</td> 
            <td>{$row['max_count']}</td> 
            <td class=\"alt\">{$row['used_count']}</td> 
            <td>{$row['last_use_index']}</td> 
            </tr> ";
            $t .= $row;
        }

        $t .= "</table> 
</body> 
</html> ";

        return $t;

    }
}