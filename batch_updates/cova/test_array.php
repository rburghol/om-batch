<?php

$a1 = array('a','b');
$a2 = array('b','a');
print("Unsorted\n");
if ($a1 == $a2) {
   print("Same\n");
} else {
   print("Different\n");
}
sort($a1);
sort($a2);
print("Sorted\n");

if ($a1 == $a2) {
   print("Same\n");
} else {
   print("Different\n");
}

?>
