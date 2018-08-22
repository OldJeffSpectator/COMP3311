<?php
// COMP3311 18s1 Assignment 2
// Functions for assignment Tasks A-E
// Written by Jeremy Chen(z5016815), May 2018
// assumes that defs.php has already been included

// Task A

function membersOf($db,$groupID)
{
	$q = "select * from acad_object_groups where id = %d";
	$grp = dbOneTuple($db, mkSQL($q, $groupID));
	$array = array();
	$finished = array();
	$nextarray = array();
	
	if ($grp["gtype"] == "subject" && $grp["gdefby"] == "enumerated"){
		$query = "select code from acad_object_groups, subject_group_members, subjects where subjects.id = subject_group_members.subject and acad_object_groups.id = subject_group_members.ao_group and (acad_object_groups.id = %d or acad_object_groups.parent = %d) group by code order by code";
		$NewQuery = dbQuery($db, mkSQL($query, $groupID, $groupID));
		while ($next = dbNext($NewQuery)) {
			array_push($array, $next[code]);
		}
		return array($grp["gtype"], $array);
	}
	elseif ($grp["gtype"] == "stream" && $grp["gdefby"] == "enumerated"){
		$query = "select code from acad_object_groups, stream_group_members, streams where streams.id = stream_group_members.stream and acad_object_groups.id = stream_group_members.ao_group and (acad_object_groups.id = %d or acad_object_groups.parent = %d) group by code order by code";
		$NewQuery = dbQuery($db, mkSQL($query, $groupID, $groupID));
		while ($next = dbNext($NewQuery)) {
			array_push($array, $next[code]);
		}
		return array($grp["gtype"], $array);
	}
	elseif ($grp["gtype"] == "program" && $grp["gdefby"] == "enumerated"){
		$query = "select code from acad_object_groups, program_group_members, programs where programs.id = program_group_members.program and acad_object_groups.id = program_group_members.ao_group and (acad_object_groups.id = %d or acad_object_groups.parent = %d) group by code order by code";
		$NewQuery = dbQuery($db, mkSQL($query, $groupID, $groupID));
		while ($next = dbNext($NewQuery)) {
			array_push($array, $next[code]);
		}
		return array($grp["gtype"], $array);
	}
	elseif ($grp["gdefby"] == "pattern"){
		
		$query = "select definition from acad_object_groups where acad_object_groups.id = %d group by definition order by definition";
		$NewQuery = dbQuery($db, mkSQL($query, $groupID));
		while ($next = dbNext($NewQuery)) {	
			if (strpos($next[definition], '!') !== false){
				return array($grp["gtype"], $nextarray);
			}
			$temp = str_replace(';',',',$next[definition]);
			$temp = str_replace('{','',$temp);
			$temp = str_replace('}','',$temp);		
			$explosion = explode(',', $temp);	
		}
		foreach ($explosion as $value) {
			if ((strpos($value, '-') !== false) && (strpos($value, '[') !== false)) {
				$to = strpos($value,"-");
				$first = $to - 1;
				$second = $to + 1;
				$number = substr($value, $first, 1);
				$secondnumber = substr($value, $second, 1);
				$initial = explode('[', $value);
				while ($number < $secondnumber + 1){
					if ($number == $secondnumber){
						$newString = $newString.$initial[0].$number;
					}
					else {
						$newString = $newString.$initial[0].$number."\n";
					}
					$number = $number + 1;
				}
				array_push($nextarray, $newString);
			}
			elseif ((strpos($value, '[') !== false) && (strpos($value, '-') == false)) {
				
				$first = strpos($value,"[");
				$second = strpos($value,"]");
				$initial = explode('[', $value);
				while ($first < $second - 1){
					$first = $first + 1;
					$number = substr($value, $first, 1);
					if ($first == $second - 1){
						$newString = $newString.$initial[0].$number;
					}
					else {
						$newString = $newString.$initial[0].$number."\n";
					}
				}
				array_push($nextarray, $newString);				
			}
			else {
				array_push($nextarray, $value);
			}
		}
		sort($nextarray);
		return array($grp["gtype"], $nextarray);
	}
}

// Task B

function inGroup($db, $code, $groupID)
{
	if (strpos($code, "GEN") !== false){
		return true;
	}
	$q = "select * from acad_object_groups where id = %d";
	$grp = dbOneTuple($db, mkSQL($q, $groupID));
	$array = array();
	$finished = array();
	$nextarray = array();
	
	if ($grp["gtype"] == "subject" && $grp["gdefby"] == "enumerated"){
		$query = "select code from acad_object_groups, subject_group_members, subjects where subjects.id = subject_group_members.subject and acad_object_groups.id = subject_group_members.ao_group and (acad_object_groups.id = %d or acad_object_groups.parent = %d) group by code order by code";
		$NewQuery = dbQuery($db, mkSQL($query, $groupID, $groupID));
		while ($next = dbNext($NewQuery)) {
			array_push($array, $next[code]);
		}
		foreach ($array as $value) {
			if (strpos($value, $code) !== false) {
				return true;
			}
		}
	}
	elseif ($grp["gtype"] == "stream" && $grp["gdefby"] == "enumerated"){
		$query = "select code from acad_object_groups, stream_group_members, streams where streams.id = stream_group_members.stream and acad_object_groups.id = stream_group_members.ao_group and (acad_object_groups.id = %d or acad_object_groups.parent = %d) group by code order by code";
		$NewQuery = dbQuery($db, mkSQL($query, $groupID, $groupID));
		while ($next = dbNext($NewQuery)) {
			array_push($array, $next[code]);
		}
		foreach ($array as $value) {
			if (strpos($value, $code) !== false) {
				return true;
			}
		}
	}
	elseif ($grp["gtype"] == "program" && $grp["gdefby"] == "enumerated"){
		$query = "select code from acad_object_groups, program_group_members, programs where programs.id = program_group_members.program and acad_object_groups.id = program_group_members.ao_group and (acad_object_groups.id = %d or acad_object_groups.parent = %d) group by code order by code";
		$NewQuery = dbQuery($db, mkSQL($query, $groupID, $groupID));
		while ($next = dbNext($NewQuery)) {
			array_push($array, $next[code]);
		}
		foreach ($array as $value) {
			if (strpos($value, $code) !== false) {
				return true;
			}
		}
	}

	elseif ($grp["gdefby"] == "pattern"){
		
		$query = "select definition from acad_object_groups where acad_object_groups.id = %d group by definition order by definition";
		$NewQuery = dbQuery($db, mkSQL($query, $groupID));
		
		while ($next = dbNext($NewQuery)) {
			
			$temp = str_replace(';',',',$next[definition]);
			$temp = str_replace('{','',$temp);
			$temp = str_replace('}','',$temp);		
			$explosion = explode(',', $temp);	
		
			foreach ($explosion as $values) {
					
				if (strpos($values, '!') !== false){
					return false;
				}			
				if (strpos($values, '#') !== false) {
					if (strpos($values, 'FREE') !== false){
						return true;
					}	
					if (strpos($code, "GEN") !== false){
						return true;
					}
					$original = $values;
					$other = $code;
					while (strpos($original, '#') !== false){
						$position = strpos($original, '#');	
						$other = substr_replace($other,'#',$position,1);
						$original = preg_replace('/#/','?', $original, 1);
						}
					if (strpos($other, $values) !== false){
						return true;
					}
				}	
			}
		}
		foreach ($explosion as $value) {
			if ((strpos($value, '-') !== false) && (strpos($value, '[') !== false)) {
				
				$to = strpos($value,"-");
				$first = $to - 1;
				$second = $to + 1;
				$number = substr($value, $first, 1);
				$secondnumber = substr($value, $second, 1);
				$initial = explode('[', $value);
				while ($number < $secondnumber + 1){
					if ($number == $secondnumber){
						$newString = $newString.$initial[0].$number;
					}
					else {
						$newString = $newString.$initial[0].$number."\n";
					}
					$number = $number + 1;
				}
				array_push($nextarray, $newString);
			}
			elseif ((strpos($value, '[') !== false) && (strpos($value, '-') == false)) {
				
				$first = strpos($value,"[");
				$second = strpos($value,"]");
				$initial = explode('[', $value);
				while ($first < $second - 1){
					
					$first = $first + 1;
					$number = substr($value, $first, 1);
					if ($first == $second - 1){
						$newString = $newString.$initial[0].$number;
					}
					else {
						$newString = $newString.$initial[0].$number."\n";
					}
				}
				array_push($nextarray, $newString);				
			}
			else {
				array_push($nextarray, $value);
			}
		}
		foreach ($nextarray as $value) {
			if (strpos($value, $code) !== false) {
				return true;
			}
		}
	}
	return false;
}

// Task C

function canSatisfy($db, $code, $ruleID, $enrolment)
{
	/*
	echo "$code\n";  //COMP1917
	echo "$ruleID\n"; //10384
	echo "$db\n"; //Resource id #8
	echo "$enrolment[0]\n"; //554 
	*/
	
	$query = "select ao_group from rules where rules.id = %d";
	$NewQuery = dbQuery($db, mkSQL($query, $ruleID));
	$array = array();
	$explosion = array();
	while ($next = dbNext($NewQuery)) {
		$array = othermembers($db, $next[ao_group]);
		}		

	foreach ($array as $value) {
			
		if ((strpos($value, 'GEN') !== false) && (strpos($code, 'GEN') !== false)) {
			$APreviousQuery = "select facultyOf(offeredBy) as agiven from programs where id = %d"; 
			
			$ANewPreviousQuery = dbQuery($db, mkSQL($APreviousQuery, $enrolment[0]));
			while ($ANext = dbNext($ANewPreviousQuery)) {
				$thenumber = $ANext[agiven];
			}
			$PreviousQuery = "select facultyOf(offeredBy) as given from subjects where code = %s"; 
			$NewPreviousQuery = dbQuery($db, mkSQL($PreviousQuery, $code));
			while ($Previous = dbNext($NewPreviousQuery)) {
				$othernumber = $Previous[given];
			}
			$NextQuery = "select facultyOf(offeredBy) as offered from streams where streams.id = %d or streams.id = %d";
			$NewNextQuery = dbQuery($db, mkSQL($NextQuery, $enrolment[1][0], $enrolment[0][1]));
			while ($next = dbNext($NewNextQuery)) {
				if ($othernumber == $next[offered]){
					return false;
				}
				if ($thenumber == $othernumber){
					return false;
				}
			}
			return true;
		}			
		elseif (strpos($value, '#') !== false) {
			$original = $value;
			$other = $code;
			while (strpos($original, '#') !== false){
				$position = strpos($original, '#');	
				$other = substr_replace($other,'#',$position,1);
				$original = preg_replace('/#/','?', $original, 1);
				}
			if (strpos($other, $value) !== false){
				return true;
			}	
		}
		elseif (strpos($value, $code) !== false){
			return true;
		}
	}
}

// Task D:

function progress($db, $stuID, $term)
{	
	$nextarray = array();
	$newArray = array();
	$emptyArray = array();
	$enrol = array();
	$ruleArray = array();
	$streamArray = array();
	$streamRule = array();
	$IDstream = array();
	$IDprogram = array();
	$overallArray = array();
	$typeStream = array();
	$typeProgram = array();
	$minProgram = array();
	$minStream = array();
	$nameProgram = array();
	$nameStream = array();
	$storage = array();
	$coldstorage = array();
	$colderstorage = array();
	$coldeststorage = array();

	$OldQuery = "select year from semesters where semesters.id = %d";
	$VeryOldQuery = dbQuery($db, mkSQL($OldQuery, $term));
	while ($next = dbNext($VeryOldQuery)) {
		$currentYear = $next[year];
		}

	$AnOldQuery = "select census from semesters where semesters.id = %d";
	$AVeryOldQuery = dbQuery($db, mkSQL($AnOldQuery, $term));
	while ($nextA = dbNext($AVeryOldQuery)) {
		$census = $nextA[census];
		//echo "$census\n";
		}			

	//echo "$stuID";	
	$Latest = "select program_enrolments.program as programs
		from program_enrolments,semesters,people 
		where semesters.id = program_enrolments.semester
		and program_enrolments.student = people.id
		and people.id = %d";
		
	$LatestQuery = dbQuery($db, mkSQL($Latest, $stuID));
	while ($TheNext = dbNext($LatestQuery)) {
		$currentPro = $TheNext[programs];
		}
	//echo "$currentPro\n";
	
	//STREAM RULES
	$TheLatest = "select stream_enrolments.stream as streams
		from program_enrolments,stream_enrolments,semesters,people 
		where semesters.id = program_enrolments.semester
		and stream_enrolments.partof = program_enrolments.id
		and program_enrolments.student = people.id
		and people.id = %d";
		
	$TheLatestQuery = dbQuery($db, mkSQL($TheLatest, $stuID));
	while ($TheTheNext = dbNext($TheLatestQuery)) {
		$currentStream = $TheTheNext[streams];
		//echo $currentStream;
		}
	//END STREAM RULES
	
	
	$NewLatest = "select rules.name as name, rules.id as id, rule, rules.type, rules.min as mini from program_rules, rules where rules.id = program_rules.rule and program_rules.program = %d";
	$NewLatestQuery = dbQuery($db, mkSQL($NewLatest, $currentPro));
	while ($TheNextRule = dbNext($NewLatestQuery)) {
		$ban = ruleName($db,$TheNextRule[rule]);
		if ((strpos($ban, 'aturity') === false)){
			//echo "$TheNextRule[mini] $TheNextRule[type]\n";
			array_push($ruleArray, $TheNextRule[rule]);
			array_push($typeProgram,$TheNextRule[type]);
			array_push($minProgram, $TheNextRule[mini]);
			array_push($nameProgram, $TheNextRule[name]);
			array_push($IDprogram, $TheNextRule[id]);
			//echo "$TheNextRule[name]\n";
		}
	}
			
	$NewLatestNew = "select rules.name as name, rules.id, rules.type, rules.min as mini, rule as morerules from stream_rules, rules where rules.id = stream_rules.rule and stream_rules.stream = %d";
	$NewLatestQueryNew = dbQuery($db, mkSQL($NewLatestNew, $currentStream));
	while ($TheNextRuleNew = dbNext($NewLatestQueryNew)) {
		$banned = ruleName($db,$TheNextRuleNew[rule]);
		if ((strpos($banned, 'aturity') === false)){
			//echo "$TheNextRuleNew[min] $TheNextRuleNew[type]\n";
			array_push($streamArray, $TheNextRuleNew[morerules]);
			array_push($typeStream,$TheNextRuleNew[type]);
			array_push($minStream, $TheNextRuleNew[mini]);
			array_push($nameStream, $TheNextRuleNew[name]);
			array_push($IDstream, $TheNextRuleNew[id]);
			//echo "$TheNextRuleNew[name]\n";
		}
	}
	$coldcounter = 0;
	foreach ($IDstream as $valued) {
			
			$storage[$valued] = 10;
			$colderstorage[$valued] = $minStream[$coldcounter];
			$coldstorage[$valued] = $nameStream[$coldcounter];
			$coldeststorage[$valued] = $typeStream[$coldcounter];
			$coldcounter = $coldcounter + 1;

		}
	$coldcounter = 0;
	foreach ($IDprogram as $values) {

			$storage[$values] = 10;
			$colderstorage[$values] = $minProgram[$coldcounter];
			$coldstorage[$values] = $nameProgram[$coldcounter];
			$coldeststorage[$values] = $typeProgram[$coldcounter];
			//echo "$values\n";
			$coldcounter = $coldcounter + 1;
		}		
	
	//FREE
	$freecap = 0;
	$anothercounter = 0;
	while ($anothercounter < 100000){
		if (($storage[$anothercounter] != NULL) && (strpos($coldeststorage[$anothercounter], 'FE') !== false)){
			$freecap = $colderstorage[$anothercounter];
			//echo "$freecap\n";
		}
		$anothercounter = $anothercounter + 1;
	}
	
	//ENDFREE

	$enrol[0] = $currentPro;	
	$enrol[1] = $emptyArray;
		
	$query = 
	"select semesters.census, subjects.code as course, concat(substr(cast(semesters.year as text),3,2), '', lower(semesters.term)) as term, subjects.name as title, mark, grade,subjects.uoc as uoc from course_enrolments,people,courses,subjects, semesters 
	where course_enrolments.course = courses.id and 
	subjects.id = courses.subject and
	semesters.id = courses.semester and	
	semesters.census <= %s and
	people.id = course_enrolments.student and 
	people.id = %d";
	$NewQuery = dbQuery($db, mkSQL($query, $census, $stuID));
	$counter = 0;
	$totalUOC = 0;
	$totalWAM = 0;
	$totalNUM = 0;
	
	//BELOW
	$CC = 0;
	$PE = 0;
	$FE = 0;
	$GE = 0;
	$LR = 0;
	$currentfree = 0;
	
	while ($run = dbNext($NewQuery)) {
		list($finalcensus,$course,$term,$title,$mark,$grade,$uoc) = $run;
		$sentence = "Fits no requirement. Does not count";
		
		if ($finalcensus == $census){
			$mark = NULL;
			$grade = NULL;
			$uoc = NULL;
		}
		
		if ($grade == 'FL'){
			$uoc = NULL;
		}
		
		if ($grade == 'UF'){
			$uoc = NULL;
		}
		
		//IMPORTANT
		$firstcounter = 0;
		foreach ($ruleArray as $values) {
			if (@canSatisfy($db, $course, $values, $enrol) == true){
				$sentence = ruleName($db,$values);
				
				$Pas = $IDprogram[$firstcounter];
				$coldstorage[$Pas] = $nameProgram[$firstcounter];
				$colderstorage[$Pas] = $minProgram[$firstcounter];
				$coldeststorage[$Pas] = $typeProgram[$firstcounter];
				$storage[$Pas] = $storage[$Pas] + $uoc;
				//echo "$storage[$Pas]  $coldstorage[$Pas]\n";
				
				break;
			}
			$firstcounter = $firstcounter + 1;
		}
		$secondcounter = 0;
		foreach ($streamArray as $morevalues) {
			
			if (@canSatisfy($db, $course, $morevalues, $enrol) == true){
				$sentence = ruleName($db,$morevalues);
				//echo "$IDstream[$secondcounter]\n";
				$Pa = $IDstream[$secondcounter];
				$coldstorage[$Pa] = $nameStream[$secondcounter];
				$colderstorage[$Pa] = $minStream[$secondcounter];
				$coldeststorage[$Pa] = $typeStream[$secondcounter];
				$storage[$Pa] = $storage[$Pa] + $uoc;
				//echo "$storage[$Pa]  $coldstorage[$Pa]\n";
				break;
			}
			$secondcounter = $secondcounter + 1;
		}

		if ($grade == 'FL'){
			$sentence = "Failed. Does not count";
			$uoc = NULL;
		}
		
		if ($grade == 'UF'){
			$sentence = "Failed. Does not count";
			$uoc = NULL;
		}
		
		if ($grade == 'SY'){
			$totalNUM = $totalNUM - 1;
		}
		
		if ($finalcensus == $census){
			$mark = NULL;
			$grade = NULL;
			$uoc = NULL;
			$totalNUM = $totalNUM - 1;
			$sentence = "Incomplete. Does not yet count";
		}
		
		if (($sentence == "Fits no requirement. Does not count") || ($sentence == "Rec Electives")) {
			
			if ($freecap - $currentfree >= $uoc){
				$sentence = "Free elect";
				$currentfree = $currentfree + $uoc;
			}
		}
		
		$totalUOC = $totalUOC + $uoc;
		$totalWAM = $totalWAM + $mark;
		$totalNUM = $totalNUM + 1;
		$newArray = array($course,$term,$title,$mark,$grade,$uoc,$sentence);
		$nextarray[$counter] = $newArray;
		$counter = $counter + 1;
		}	
	
		if ($totalNUM == 0){
			$overallArray[1] = NULL;
			$overallArray[2] = NULL;
		}
		else {
			$overallArray[1] = $totalWAM /$totalNUM;
			$overallArray[2] = $totalUOC;
		}
		
		$overallArray[0] = "Overall WAM";
		$nextarray[$counter] = $overallArray;
		$counter = $counter + 1;
		//OVERALL WAM
		
		$anothercounter = 0;
		while ($anothercounter < 100000){
			if (($storage[$anothercounter] != NULL) && (strpos($coldeststorage[$anothercounter], 'CC') !== false) && ($colderstorage[$anothercounter] - $storage[$anothercounter] + 10 != 0)){
				$uocValue = $storage[$anothercounter] - 10;
				$nameValue = $coldstorage[$anothercounter];
				$minValue = $colderstorage[$anothercounter];
				$required = $minValue - $uocValue;
				$sub = array("$uocValue UOC so far; need $required UOC more", "$nameValue");
				$nextarray[$counter] = $sub;
				$counter = $counter + 1;
			}
			$anothercounter = $anothercounter + 1;
		}
		$anothercounter = 0;
		while ($anothercounter < 100000){
			if (($storage[$anothercounter] != NULL) && (strpos($coldeststorage[$anothercounter], 'PE') !== false) && ($colderstorage[$anothercounter] - $storage[$anothercounter] + 10 != 0)){
				$uocValue = $storage[$anothercounter] - 10;
				$nameValue = $coldstorage[$anothercounter];
				$minValue = $colderstorage[$anothercounter];
				$required = $minValue - $uocValue;
				$sub = array("$uocValue UOC so far; need $required UOC more", "$nameValue");
				$nextarray[$counter] = $sub;
				$counter = $counter + 1;
			}
			$anothercounter = $anothercounter + 1;
		}
		$anothercounter = 0;
		while ($anothercounter < 100000){
			if (($storage[$anothercounter] != NULL) && (strpos($coldeststorage[$anothercounter], 'FE') !== false) && ($freecap - $currentfree != 0)){
				$uocValue = $storage[$anothercounter] - 10;
				$nameValue = $coldstorage[$anothercounter];
				$minValue = $colderstorage[$anothercounter];
				$uocValue = $uocValue + $currentfree;
				$required = $freecap - $currentfree;
				$sub = array("$uocValue UOC so far; need $required UOC more", "$nameValue");
				$nextarray[$counter] = $sub;
				$counter = $counter + 1;
			}
			$anothercounter = $anothercounter + 1;
		}
		$anothercounter = 0;
		while ($anothercounter < 100000){
			if (($storage[$anothercounter] != NULL) && (strpos($coldeststorage[$anothercounter], 'GE') !== false) && ($colderstorage[$anothercounter] - $storage[$anothercounter] + 10 != 0)){
				$uocValue = $storage[$anothercounter] - 10;
				$nameValue = $coldstorage[$anothercounter];
				$minValue = $colderstorage[$anothercounter];
				$required = $minValue - $uocValue;
				$sub = array("$uocValue UOC so far; need $required UOC more", "$nameValue");
				$nextarray[$counter] = $sub;
				$counter = $counter + 1;
			}
			$anothercounter = $anothercounter + 1;
		}
		$anothercounter = 0;
		while ($anothercounter < 100000){
			if (($storage[$anothercounter] != NULL) && (strpos($coldeststorage[$anothercounter], 'LR') !== false) && ($colderstorage[$anothercounter] - $storage[$anothercounter] + 10 != 0)){
				$uocValue = $storage[$anothercounter] - 10;
				$nameValue = $coldstorage[$anothercounter];
				$minValue = $colderstorage[$anothercounter];
				$required = $minValue - $uocValue;
				$sub = array("$uocValue UOC so far; need $required UOC more", "$nameValue");
				$nextarray[$counter] = $sub;
				$counter = $counter + 1;
			}
			$anothercounter = $anothercounter + 1;
		}
		//$sub = array("$CC UOC so far; need 9 UOC more", "CC");
		//$nextarray[$counter] = $sub;
		
	return $nextarray; // stub
}


// Task E:

function advice($db, $studentID, $currTermID, $nextTermID)
{
	$nextarray = array();
	$newArray = array();
	$emptyArray = array();
	$enrol = array();
	$ruleArray = array();
	$streamArray = array();
	$streamRule = array();
	$IDstream = array();
	$IDprogram = array();
	$overallArray = array();
	$typeStream = array();
	$typeProgram = array();
	$minProgram = array();
	$minStream = array();
	$nameProgram = array();
	$nameStream = array();
	$storage = array();
	$coldstorage = array();
	$colderstorage = array();
	$coldeststorage = array();
	
	//STRATEGY
	/*
	COURSES WITH EXCLUSIONS
	*/
	//echo "$nextTermID\n";
	$AnOldQuery = "select census from semesters where semesters.id = %d";
	$AVeryOldQuery = dbQuery($db, mkSQL($AnOldQuery, $currTermID));
	while ($nextA = dbNext($AVeryOldQuery)) {
		$census = $nextA[census];
		}	
	
	$Latest = "select program_enrolments.program as programs
		from program_enrolments,semesters,people 
		where semesters.id = program_enrolments.semester
		and program_enrolments.student = people.id
		and people.id = %d
		";
		
	$LatestQuery = dbQuery($db, mkSQL($Latest, $studentID));
	while ($TheNext = dbNext($LatestQuery)) {
		$currentPro = $TheNext[programs];
		}

	$TheLatest = "select stream_enrolments.stream as streams
		from program_enrolments,stream_enrolments,semesters,people 
		where semesters.id = program_enrolments.semester
		and stream_enrolments.partof = program_enrolments.id
		and program_enrolments.student = people.id
		and people.id = %d
		";
		
	$TheLatestQuery = dbQuery($db, mkSQL($TheLatest, $studentID));
	while ($TheTheNext = dbNext($TheLatestQuery)) {
		$currentStream = $TheTheNext[streams];
		}
	
	//RULES
	$NewLatest = "select rules.name as name, rules.id as id, rule, rules.type, rules.min as mini from program_rules, rules where rules.id = program_rules.rule and program_rules.program = %d";
	$NewLatestQuery = dbQuery($db, mkSQL($NewLatest, $currentPro));
	while ($TheNextRule = dbNext($NewLatestQuery)) {
		$ban = ruleName($db,$TheNextRule[rule]);

		if (((strpos($ban, 'GE') !== false) && (strpos($ban, 'aturity') === false)) || (strpos($ban, 'Gen') !== false)){
			 $string = $ban;
		}
		
			//echo "$TheNextRule[mini] $TheNextRule[type]\n";
		array_push($ruleArray, $TheNextRule[rule]);
		array_push($typeProgram,$TheNextRule[type]);
		array_push($minProgram, $TheNextRule[mini]);
		array_push($nameProgram, $TheNextRule[name]);
		array_push($IDprogram, $TheNextRule[id]);
			//echo "$TheNextRule[name]\n";
		
	}
			
	$NewLatestNew = "select rules.name as name, rules.id, rules.type, rules.min as mini, rule as morerules from stream_rules, rules where rules.id = stream_rules.rule and stream_rules.stream = %d";
	$NewLatestQueryNew = dbQuery($db, mkSQL($NewLatestNew, $currentStream));
	while ($TheNextRuleNew = dbNext($NewLatestQueryNew)) {
		$banned = ruleName($db,$TheNextRuleNew[morerules]);
		
			//echo "$TheNextRuleNew[min] $TheNextRuleNew[type]\n";
		array_push($streamArray, $TheNextRuleNew[morerules]);
		array_push($typeStream,$TheNextRuleNew[type]);
		array_push($minStream, $TheNextRuleNew[mini]);
		array_push($nameStream, $TheNextRuleNew[name]);
		array_push($IDstream, $TheNextRuleNew[id]);
			//echo "$TheNextRuleNew[name]\n";
		
	}
	
	$coldcounter = 0;
	foreach ($IDstream as $valued) {
			
			$storage[$valued] = 10;
			$colderstorage[$valued] = $minStream[$coldcounter];
			$coldstorage[$valued] = $nameStream[$coldcounter];
			$coldeststorage[$valued] = $typeStream[$coldcounter];
			$coldcounter = $coldcounter + 1;

		}
	$coldcounter = 0;
	foreach ($IDprogram as $values) {

			$storage[$values] = 10;
			$colderstorage[$values] = $minProgram[$coldcounter];
			$coldstorage[$values] = $nameProgram[$coldcounter];
			$coldeststorage[$values] = $typeProgram[$coldcounter];
			//echo "$values\n";
			$coldcounter = $coldcounter + 1;
		}		
		
	//RULES END	
	//COURSES COMPLETED (NOT FAILED)
	$query = 
	"select semesters.census, subjects.code as course, concat(substr(cast(semesters.year as text),3,2), '', lower(semesters.term)) as term, subjects.name as title, mark, grade,subjects.uoc as uoc from course_enrolments,people,courses,subjects, semesters 
	where course_enrolments.course = courses.id and 
	subjects.id = courses.subject and
	semesters.id = courses.semester and	
	semesters.census <= %s and
	people.id = course_enrolments.student and 
	people.id = %d";
	//RULES FOR EACH COURSE
	$NewQuery = dbQuery($db, mkSQL($query, $census, $studentID));
	$completed = array();
	$othercomplete = array();
	//PASSED (MODIFY)
	$passed = array();
	$alluoc = 0;
	while ($run = dbNext($NewQuery)) {
		list($finalcensus,$course,$term,$title,$mark,$grade,$uoc) = $run;
		//echo "$course\n";
		if ($grade != 'FL'){
			//echo "$grade\n";
			array_push($passed, $course);
			$alluoc = $alluoc + $uoc; 
		}
		if (($grade == 'FL') && ($finalcensus == $census)){
			//echo "$grade\n";
			array_push($passed, $course);
			$alluoc = $alluoc + $uoc; 
		}
		array_push($completed, $course);
		array_push($othercomplete, $course);
	}
	//echo "$alluoc\n";
	$thecounter = 0;
	$switch = 0;
	$remaining = array();
	$safe = array();
	//REMAINING
	foreach ($IDstream as $valuable) {
		$query = "select ao_group from rules where rules.id = %d";
		$NewQuery = dbQuery($db, mkSQL($query, $valuable));
		$array = array();
		while ($next = dbNext($NewQuery)) {
			$array = othermembers($db, $next[ao_group]);
			foreach ($array as $values) {
				foreach ($completed as $enigma){
					if ($values == $enigma){
						$switch = 1;
					}
				}
				if ($switch == 0){	
					//echo "$values $valuable\n";
					array_push($remaining, $values);
					array_push($safe, $valuable);
				}
				$switch = 0;
			}
		}		
	}
	//MATURITY
	$thecounter = 0;
	$switch = 0;
	$pattern = array();
	$unsafe = array();
	foreach ($IDprogram as $valuable) {
		//echo "$valuable\n";
		$query = "select ao_group from rules where rules.id = %d";
		$NewQuery = dbQuery($db, mkSQL($query, $valuable));
		$array = array();
		while ($next = dbNext($NewQuery)) {
			$array = othermembers($db, $next[ao_group]);
			if (is_array($array) || is_object($array)){
				foreach ($array as $values) {
					
					foreach ($completed as $enigma){
						if ($values == $enigma){
							$switch = 1;
						}
					}
					if ($switch == 0){	
						
						//$ban = ruleName($db,$valuable);
						//echo "$values\n";
						array_push($pattern, $values);
						array_push($unsafe, $valuable);
						array_push($remaining, $values);
						array_push($safe, $valuable);
					}
					$switch = 0;
				}
			}
		}		
	}
	array_push($pattern, '####3');
	array_push($unsafe, 11465);
	array_push($pattern, '####4');
	array_push($unsafe, 11466);
	array_push($pattern, '####6');
	array_push($unsafe, 11466);
	
	/*
	foreach ($pattern as $enigma){
		echo "$enigma\n";
	}
	*/

	//COURSES WITH PREREQS (FILTER FOR PREREQS NEEDED)
	$switch = 0;
	$switchcounter = 0;
	foreach ($remaining as $valid) {
					
			$GreatQuery = "	select acad_object_groups.definition as def from subjects, subject_prereqs, rules, acad_object_groups where subject_prereqs.subject = subjects.id and rules.id = subject_prereqs.rule and acad_object_groups.id = rules.ao_group and subjects.code = %s";
			$MyGreatQuery = dbQuery($db, mkSQL($GreatQuery, $valid));
			while ($run = dbNext($MyGreatQuery)) {
				$switch = 0;
				if ($run[def] != NULL){
					foreach ($completed as $invalid){
						if ((strpos($run[def], $invalid) !== false)){
							$switch = 1;
						}
					}
					if ($switch == 0){
						$remaining[$switchcounter] = NULL;
					}
				}
			}
			$switchcounter = $switchcounter + 1;
		}

	//COURSES OFFERED NEXT SEM
	$switch = 0;
	$switchcounter = 0;
	$news = array();
	$result = array();
	//echo "$nextTermID";
	foreach ($remaining as $alright){
		//echo "$alright\n";
		
		$myQuery = "select subjects.code as code from subjects, semesters, courses 
		where courses.semester = semesters.id and
		semesters.id = %d and
		courses.subject = subjects.id and 
		subjects.code = %s";
		$MyNewQuery = dbQuery($db, mkSQL($myQuery, $nextTermID, $alright));
		while ($nextB = dbNext($MyNewQuery)) {
			array_push($news, $nextB[code]);				
		}
		$switchcounter = $switchcounter + 1;		
	}
	$switch = 0;
	$switchcounter = 0;
	foreach ($remaining as $decent){
		$switch = 0;
		foreach ($news as $meh){
			if ($meh == $decent){
				//echo "$meh\n";
				$switch = 1;
			}
		}
		if ($switch == 0){
			$remaining[$switchcounter] = NULL;
		}
		$switchcounter = $switchcounter + 1;
	}
			
	//COURSES WITH EXCLUSIONS
	$switch = 0;
	$switchcounter = 0;
	$excluded = array();
	$theresult = array();
	
	foreach ($othercomplete as $values){
		$Queries = "select definition from subjects, acad_object_groups where subjects.code = %s and acad_object_groups.id = subjects.excluded";
		$TheseQuery = dbQuery($db, mkSQL($Queries, $values));
		while ($run = dbNext($TheseQuery)) {
			array_push($excluded, $run[definition]);
		}
	}
	foreach ($remaining as $valuable) {
		foreach ($excluded as $val){
			if ((strpos($val, $valuable) !== false)){
				$switch = 1;
			}
		}
		if ($switch == 1){
			$remaining[$switchcounter] = NULL;
			$switch = 0;
		}
		$switchcounter = $switchcounter + 1;	
	}
	//COURSES THAT REQUIRE MATURITY
	
	$remainingcounter = 0;
	foreach ($remaining as $valuable) {
		$patterncounter = 0;
		foreach ($pattern as $invaluable){
			if (strpos($invaluable, '#') !== false) {
				$original = $invaluable;
				$other = $valuable;
				while (strpos($original, '#') !== false){
					$position = strpos($original, '#');	
					$other = substr_replace($other,'#',$position,1);
					$original = preg_replace('/#/','?', $original, 1);
					}
				if (strpos($other, $invaluable) !== false){
					
					$ban = ruleName($db,$unsafe[$patterncounter]);
					if ((strpos($ban, 'Level 2') !== false)){
						//echo "$ban\n";
						if ($alluoc < 48){
							$remaining[$remainingcounter] = NULL;
						}
					}	
					elseif ((strpos($ban, 'Level 3') !== false)){
						//echo "$ban\n";
						if ($alluoc < 84){
							$remaining[$remainingcounter] = NULL;
						}
					}
					elseif ((strpos($ban, 'Level 4') !== false)){
						//echo "$ban\n";
						if ($alluoc < 108){
							$remaining[$remainingcounter] = NULL;
						}
					}					
				}	
			}
			elseif (strpos($invaluable, $valuable) !== false){
				//echo "$invaluable $valuable\n";
			}
			$patterncounter = $patterncounter + 1;
		}
		$remainingcounter = $remainingcounter + 1;
	
	}
	//COURSE TITLE UOC REQUIREMENTS	
	$finalarray = array();
	$count = 0;
	$remaincount = 0;
	foreach ($remaining as $valuable) {
		$ThisQuery = "select subjects.code, subjects.name, subjects.uoc from subjects where subjects.code = '$valuable'";
		$MyThisQuery = dbQuery($db, mkSQL($ThisQuery, $nextTermID));
		while ($run = dbNext($MyThisQuery)) {
			list($code,$name,$uoc) = $run;
			$identify = $safe[$count];
			$TheName = ruleName($db,$identify);
			
			if(strpos($TheName, 'Rec Electives') === false){ 
				$another = array($code, $name, $uoc, $TheName);
				array_push($finalarray, $another);
			}
			if(strpos($TheName, 'Rec Electives') !== false){ 
				$remaining[$remaincount] = NULL;
			}
		}
		$count = $count + 1;
		$remaincount = $remaincount + 1;
	}
	//FREE
	$freecap = 0;
	$anothercounter = 0;
	while ($anothercounter < 100000){
		if (($storage[$anothercounter] != NULL) && (strpos($coldeststorage[$anothercounter], 'FE') !== false)){
			$freecap = $colderstorage[$anothercounter];
			//echo "$freecap\n";
		}
		$anothercounter = $anothercounter + 1;
	}
	//ENDFREE
	//REPEATED
	$Tquery = 
	"select semesters.census, subjects.code as course, concat(substr(cast(semesters.year as text),3,2), '', lower(semesters.term)) as term, subjects.name as title, mark, grade,subjects.uoc as uoc from course_enrolments,people,courses,subjects, semesters 
	where course_enrolments.course = courses.id and 
	subjects.id = courses.subject and
	semesters.id = courses.semester and	
	semesters.census <= %s and
	people.id = course_enrolments.student and 
	people.id = %d";
	//REPEATED
	$gencounter = 0;
	$NewtQuery = dbQuery($db, mkSQL($Tquery, $census, $studentID));
	$currentfree = 0;
	while ($run = dbNext($NewtQuery)) {
		list($finalcensus,$course,$term,$title,$mark,$grade,$uoc) = $run;
		
		if ((strpos($course, 'GEN') !== false) && ($grade != 'FL') && ($finalcensus != $census)){
			$gencounter = $gencounter + $uoc;
		}
		elseif ((strpos($course, 'GEN') !== false) && ($finalcensus == $census)){
			$gencounter = $gencounter + $uoc;
		}
		//echo "$studentID";
		//echo "$course $grade $mark $uoc\n";
		$sentence = "Fits no requirement. Does not count";
		if ($grade == 'FL'){
			$uoc = NULL;
		}
		if ($grade == 'UF'){
			$uoc = NULL;
		}
		$firstcounter = 0;
		foreach ($ruleArray as $values) {
			if (@canSatisfy($db, $course, $values, $enrol) == true){
				$sentence = ruleName($db,$values);
				$Pas = $IDprogram[$firstcounter];
				$coldstorage[$Pas] = $nameProgram[$firstcounter];
				$colderstorage[$Pas] = $minProgram[$firstcounter];
				$coldeststorage[$Pas] = $typeProgram[$firstcounter];
				$storage[$Pas] = $storage[$Pas] + $uoc;
				break;
			}
			$firstcounter = $firstcounter + 1;
		}
		$secondcounter = 0;
		foreach ($streamArray as $morevalues) {
			
			if (@canSatisfy($db, $course, $morevalues, $enrol) == true){
				$sentence = ruleName($db,$morevalues);
				$Pa = $IDstream[$secondcounter];
				$coldstorage[$Pa] = $nameStream[$secondcounter];
				$colderstorage[$Pa] = $minStream[$secondcounter];
				$coldeststorage[$Pa] = $typeStream[$secondcounter];
				$storage[$Pa] = $storage[$Pa] + $uoc;
				break;
			}
			$secondcounter = $secondcounter + 1;
		}
		if ($grade == 'SY'){
			$totalNUM = $totalNUM - 1;
		}
		if (($sentence == "Fits no requirement. Does not count") || ($sentence == "Rec Electives")) {
			
			if ($freecap - $currentfree >= $uoc){
				$sentence = "Free elect";
				$currentfree = $currentfree + $uoc;
			}
		}	
		//echo "$currentfree\n";
	}
	//HOW MANY MORE UOC REQUIRED
	$anothercounter = 0;
		while ($anothercounter < 100000){
			if (($storage[$anothercounter] != NULL) && (strpos($coldeststorage[$anothercounter], 'CC') !== false) && ($colderstorage[$anothercounter] - $storage[$anothercounter] + 10 != 0)){
				$uocValue = $storage[$anothercounter] - 10;
				$nameValue = $coldstorage[$anothercounter];
				$minValue = $colderstorage[$anothercounter];
				$required = $minValue - $uocValue;
				//echo "$uocValue UOC so far; need $required UOC more $nameValue\n";
			}
			$anothercounter = $anothercounter + 1;
		}
		$anothercounter = 0;
		while ($anothercounter < 100000){
			if (($storage[$anothercounter] != NULL) && (strpos($coldeststorage[$anothercounter], 'PE') !== false) && ($colderstorage[$anothercounter] - $storage[$anothercounter] + 10 != 0)){
				$uocValue = $storage[$anothercounter] - 10;
				$nameValue = $coldstorage[$anothercounter];
				$minValue = $colderstorage[$anothercounter];
				$required = $minValue - $uocValue;
				//echo "$uocValue UOC so far; need $required UOC more $nameValue\n";
			}
			$anothercounter = $anothercounter + 1;
		}
		$anothercounter = 0;
		while ($anothercounter < 100000){
			if (($storage[$anothercounter] != NULL) && (strpos($coldeststorage[$anothercounter], 'FE') !== false) && ($freecap - $currentfree != 0)){
				$uocValue = $storage[$anothercounter] - 10;
				$nameValue = $coldstorage[$anothercounter];
				$minValue = $colderstorage[$anothercounter];
				$uocValue = $uocValue + $currentfree;
				$required = $freecap - $currentfree;
				//echo "$uocValue UOC so far; need $required UOC more $nameValue\n";
			}
			$anothercounter = $anothercounter + 1;
		}
		$anothercounter = 0;
		while ($anothercounter < 100000){
			if (($storage[$anothercounter] != NULL) && (strpos($coldeststorage[$anothercounter], 'GE') !== false) && ($colderstorage[$anothercounter] - $storage[$anothercounter] + 10 != 0)){
				$uocValue = $storage[$anothercounter] - 10;
				$nameValue = $coldstorage[$anothercounter];
				$minValue = $colderstorage[$anothercounter];
				$required = $minValue - $uocValue;
				//echo "$uocValue UOC so far; need $required UOC more $nameValue\n";
			}
			$anothercounter = $anothercounter + 1;
		}
		$anothercounter = 0;
		while ($anothercounter < 100000){
			if (($storage[$anothercounter] != NULL) && (strpos($coldeststorage[$anothercounter], 'LR') !== false) && ($colderstorage[$anothercounter] - $storage[$anothercounter] + 10 != 0)){
				$uocValue = $storage[$anothercounter] - 10;
				$nameValue = $coldstorage[$anothercounter];
				$minValue = $colderstorage[$anothercounter];
				$required = $minValue - $uocValue;
				//echo "$uocValue UOC so far; need $required UOC more $nameValue\n";
			}
			$anothercounter = $anothercounter + 1;
		}
	if (($freecap - $currentfree) > 0){
		$required = $freecap - $currentfree;
		$another = array("Free....", "Free Electives (many choices)", $required, "Free elect");
		array_push($finalarray, $another);
	}
	//echo "$alluoc\n";
	if (($alluoc >= 48) && ($gencounter < 12)){
		$genrequired = 12 - $gencounter;
		$another = array("GenEd...", "General Education (many choices)", $genrequired, $string);
		array_push($finalarray, $another);
	}
	
	return $finalarray; // stub
}

// ADDITIONAL Functions

function othermembers($db,$groupID)
{
	$q = "select * from acad_object_groups where id = %d";
	$grp = dbOneTuple($db, mkSQL($q, $groupID));
	$array = array();
	$finished = array();
	$nextarray = array();
	
	if ($grp["gtype"] == "subject" && $grp["gdefby"] == "enumerated"){
		$query = "select code from acad_object_groups, subject_group_members, subjects where subjects.id = subject_group_members.subject and acad_object_groups.id = subject_group_members.ao_group and (acad_object_groups.id = %d or acad_object_groups.parent = %d) group by code order by code";
		$NewQuery = dbQuery($db, mkSQL($query, $groupID, $groupID));
		while ($next = dbNext($NewQuery)) {
			array_push($array, $next[code]);
		}
		return $array;
	}
	elseif ($grp["gtype"] == "stream" && $grp["gdefby"] == "enumerated"){
		$query = "select code from acad_object_groups, stream_group_members, streams where streams.id = stream_group_members.stream and acad_object_groups.id = stream_group_members.ao_group and (acad_object_groups.id = %d or acad_object_groups.parent = %d) group by code order by code";
		$NewQuery = dbQuery($db, mkSQL($query, $groupID, $groupID));
		while ($next = dbNext($NewQuery)) {
			array_push($array, $next[code]);
		}
		return $array;
	}
	elseif ($grp["gtype"] == "program" && $grp["gdefby"] == "enumerated"){
		$query = "select code from acad_object_groups, program_group_members, programs where programs.id = program_group_members.program and acad_object_groups.id = program_group_members.ao_group and (acad_object_groups.id = %d or acad_object_groups.parent = %d) group by code order by code";
		$NewQuery = dbQuery($db, mkSQL($query, $groupID, $groupID));
		while ($next = dbNext($NewQuery)) {
			array_push($array, $next[code]);
		}
		return $array;
	}
	elseif ($grp["gdefby"] == "pattern"){
		
		$query = "select definition from acad_object_groups where acad_object_groups.id = %d group by definition order by definition";
		$NewQuery = dbQuery($db, mkSQL($query, $groupID));
		while ($next = dbNext($NewQuery)) {	
			if (strpos($value, '!') !== false){
				return array($grp["gtype"], $nextarray);
			}
			$temp = str_replace(';',',',$next[definition]);
			$temp = str_replace('{','',$temp);
			$temp = str_replace('}','',$temp);		
			$explosion = explode(',', $temp);	
		}
		foreach ($explosion as $value) {
			if ((strpos($value, '-') !== false) && (strpos($value, '[') !== false)) {
				$to = strpos($value,"-");
				$first = $to - 1;
				$second = $to + 1;
				$number = substr($value, $first, 1);
				$secondnumber = substr($value, $second, 1);
				$initial = explode('[', $value);
				while ($number < $secondnumber + 1){
					if ($number == $secondnumber){
						$newString = $newString.$initial[0].$number;
					}
					else {
						$newString = $newString.$initial[0].$number."\n";
					}
					$number = $number + 1;
				}
				array_push($nextarray, $newString);
			}
			elseif ((strpos($value, '[') !== false) && (strpos($value, '-') == false)) {
				
				$first = strpos($value,"[");
				$second = strpos($value,"]");
				$initial = explode('[', $value);
				while ($first < $second - 1){
					$first = $first + 1;
					$number = substr($value, $first, 1);
					if ($first == $second - 1){
						$newString = $newString.$initial[0].$number;
					}
					else {
						$newString = $newString.$initial[0].$number."\n";
					}
				}
				array_push($nextarray, $newString);				
			}
			else {
				array_push($nextarray, $value);
			}
		}
		sort($nextarray);
		return $nextarray;
	}
}





?>
