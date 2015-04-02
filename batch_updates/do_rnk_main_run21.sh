#!/bin/sh
# Set wd src to Curent WSP
for i in 328475 328463 328437 328869 328211 328175 328511 328571 328559 328547 328187 328583 328559; do
   php batchedit_subcomp_props.php 95 "wd_src" "equation=4" $i "" cova_fe_project
done

# Set wd_src = Custom for these 3
php batchedit_subcomp_props.php 95 "wd_src" "equation=1" 328373 
php batchedit_subcomp_props.php 95 "wd_src" "equation=1" 328321 
php batchedit_subcomp_props.php 95 "wd_src" "equation=1" 328199

php batchedit_subcomp_props.php 95 "safeyield_mgd" "demand_eqn=((7.4 * 365) * monthly_pct) / modays" 328321
# * Wayside Park wd_wsp_future_mgd - 24.0 and wsp_current_mgd - 22.2
php batchedit_subcomp_props.php 95 "safeyield_mgd" "demand_eqn=((1.4 * 365) * monthly_pct) / modays" 328199
# * Carvins Cove 14.8, 16
php batchedit_subcomp_props.php 95 "safeyield_mgd" "demand_eqn=((14.8 * 365) * monthly_pct) / modays" 328373 
w_clear_rundata 251491 21
sudo echo
sudo -u apache nohup php run_shakeTree.php 4 OR7_8470_8490 21 1984-01-01 2005-12-31 2013-08-08 0 37 -1 5 cached_wsp "runtype=cached_wsp&cache_runid=5&scenarioid=95" &
