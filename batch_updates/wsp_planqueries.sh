#!/bin/sh
template=326359 
for j in VAC25-780-80.B.6 VAC25-780-80.B.8 VAC25-780-80.B.1-3 VAC25-780-80.B.7; do
   echo "Copying $i"
   php copy_subcomps.php $template $1 $j
   php copy_subcomps.php $template $1 plans_$j
done
