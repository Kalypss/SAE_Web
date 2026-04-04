<?php
    session_start();
    switch ($_SESSION["type"]) {
        case 'soignant':
            $nb = 5;
            break;
        case 'boutique':
            $nb = 4;
            break;
        default:
            $nb = 2;
            break;
    }
?>

*{
    margin: 0%;
    padding: 0%;
    background-color: tan;
}

nav{
    width: 100%;
    margin: 0 auto;
    position: sticky;
    top: 0px;
}
nav ul{
    list-style-type: none;
    color: aquamarine;

}
nav ul li{
    float: left;
    width: <?php  echo 100/$nb."%";  ?> ;
    text-align: center;
}
nav ul::after{
    content:"";
    display:table;
    clear:both;
}
nav a{
    display: block;
    text-decoration: none;
    background-color: beige;
    padding: 30px 0;
    color:black;
}
nav a:hover{
    background-color:coral;
}
.truc{
    display:none;
    width: <?php  echo 100/$nb."%";  ?> ;
    margin: 0 ;
    position:absolute;
    z.index:1000;
}
nav > ul li:hover .truc{
    display:block;
}
.truc li{
    float:none;
    width:100%;
}

.test{
    display:flex;
    justify-content:center;
}
table{
    border-collapse: collapse;
    border: 3px solid black;
    width:90%;
    text-align: center;
}
table tr th{
    background-color:brown;
    padding-top:10px;
    padding-bottom:10px;
}
table tr td{
    background-color:burlywood;
    width:10%;
    padding-bottom:5px;
}
input{
    background-color:white;
}