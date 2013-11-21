<?php
/**
 * class.limousinActMonExpDashlet.pmFunctions.php
 *
 * ProcessMaker Open Source Edition
 * Copyright (C) 2004 - 2008 Colosa Inc.
 * *
 */

////////////////////////////////////////////////////
// limousinActMonExpDashlet PM Functions
//
// Copyright (C) 2007 COLOSA
//
// License: LGPL, see LICENSE
////////////////////////////////////////////////////

function limousinActMonExpDashlet_getMyCurrentDate()
{
	return G::CurDate('Y-m-d');
}

function limousinActMonExpDashlet_getMyCurrentTime()
{
	return G::CurDate('H:i:s');
}
