#!/bin/sh
# 4 sections:
# 1. STANDARD SETTINGS
# 2. CUSTOM SETTINGS
# 3. CLEAR PREVIOUS RUNS
# 4. DO RUN

# ********** STANDARD SETTINGS: Do Demand Settings for Standard Components ***********
# set wd_src to WSP Future
# Roanoke Main Stem
#for i in 328199 328475 328463 328437 328869 328211 328175 328511 328571 328559 328547 328187 328583 328559; do
# Roanoke Main stem from Wayside, Dan River from Danville
for i in 328199 328475 328463 328437 328869 328211 328175 328511 328571 328559 328547 328187 328583 328559 328977 328655 328773 328631 328643; do
   php batchedit_subcomp_props.php 95 "wd_src" "equation=5" $i "" cova_fe_project
done
# OR all of them
# php batchedit_subcomp_props.php 95 "wd_src" "equation=5" "" "" cova_fe_project


# ********** CUSTOM COMPONENT SETTINGS ***********
# set wd_src = custom for these 3
#php batchedit_subcomp_props.php 95 "wd_src" "equation=1" 328373 
#php batchedit_subcomp_props.php 95 "wd_src" "equation=1" 328321 
#php batchedit_subcomp_props.php 95 "wd_src" "equation=1" 328199

#php batchedit_subcomp_props.php 95 "safeyield_mgd" "demand_eqn=((8.0 * 365) * monthly_pct) / modays" 328321
# * Wayside Park wd_wsp_future_mgd - 24.0 and wsp_current_mgd - 22.2
#php batchedit_subcomp_props.php 95 "safeyield_mgd" "demand_eqn=((3.2 * 365) * monthly_pct) / modays" 328199
# * Carvins Cove 14.8, 16
#php batchedit_subcomp_props.php 95 "safeyield_mgd" "demand_eqn=((16.0 * 365) * monthly_pct) / modays" 328373 

# ********** CLEAR DATA FROM PREVIOUS RUNS ***********
# uncomment to clear data from some upstream point
#w_clear_rundata 251491 22
sudo echo
sudo -u apache nohup php run_shakeTree.php 4 OR7_8470_8490 22 1984-01-01 2005-12-31 2013-08-08 0 37 -1 5 cached_wsp "runtype=cached_wsp&cache_runid=5&scenarioid=95" &
