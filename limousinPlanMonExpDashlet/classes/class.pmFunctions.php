<?php
/**
 * class.limousinPlanMonExpDashlet.pmFunctions.php
 *
 * ProcessMaker Open Source Edition
 * Copyright (C) 2004 - 2008 Colosa Inc.
 * *
 */

////////////////////////////////////////////////////
// limousinPlanMonExpDashlet PM Functions
//
// Copyright (C) 2007 COLOSA
//
// License: LGPL, see LICENSE
////////////////////////////////////////////////////

function limousinPlanMonExpDashlet_getMyCurrentDate()
{
	return G::CurDate('Y-m-d');
}

function limousinPlanMonExpDashlet_getMyCurrentTime()
{
	return G::CurDate('H:i:s');
}
