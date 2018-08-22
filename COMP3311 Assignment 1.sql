-- COMP3311 18s1 Assignment 1
-- Written by Jeremy Chen (z5016815), April 2018

-- Q1: ...

create or replace view Q1(unswid, name)
as
select unswid, name from people join course_enrolments on course_enrolments.student = people.id group by unswid, name having count(student) > 65 
;

-- Q2: ...

create or replace view Q2(nstudents, nstaff, nboth)
as
select
(select count(students.id) as nstudents from students left join staff on staff.id = students.id where staff.id is NULL), 
(select count(staff.id) as nstaff from staff left join students on staff.id = students.id where students.id is NULL),
(select count(students.id) as nstaff from staff left join students on staff.id = students.id where students.id = staff.id)
;

-- Q3: ...

create or replace view Q3(name, ncourses)
as

select people.name, count(course_staff.course) as ncourses from course_staff join staff_roles on staff_roles.id = course_staff.role 
AND staff_roles.name = 'Course Convenor' join people on people.id = course_staff.staff group by people.name order by count(course_staff.course) desc limit 1;


-- Q4: ...

create or replace view Q4a(id)
as
select people.unswid as id from programs 
inner join program_enrolments on programs.id = program_enrolments.program 
inner join semesters on program_enrolments.semester = semesters.id 
inner join people on program_enrolments.student = people.id
where code = '3978' and semesters.year = 2005 and semesters.term = 'S2';


create or replace view Q4b(id)
as
select people.unswid as id from program_enrolments
inner join stream_enrolments on program_enrolments.id = stream_enrolments.partof
inner join streams on streams.id = stream_enrolments.stream
inner join semesters on program_enrolments.semester = semesters.id 
inner join people on program_enrolments.student = people.id
where streams.code = 'SENGA1' and semesters.year = 2005 and semesters.term = 'S2';


create or replace view Q4c(id)
as
select people.unswid as id from programs 
inner join program_enrolments on programs.id = program_enrolments.program 
inner join semesters on program_enrolments.semester = semesters.id 
inner join people on program_enrolments.student = people.id
inner join orgunits on programs.offeredby = orgunits.id
where orgunits.unswid = 'COMPSC' and semesters.year = 2005 and semesters.term = 'S2';

-- Q5: ...

create or replace view Q5(name)
as
select orgunits.name as faculties from orgunits
inner join orgunit_groups on orgunit_groups.member = orgunits.id
inner join (select facultyOf(orgunit_groups.owner) as temp from orgunits
inner join orgunit_types on orgunit_types.id = orgunits.utype
inner join orgunit_groups on orgunit_groups.member = orgunits.id
where orgunit_types.name = 'Committee') as newtable on newtable.temp = orgunits.id
group by orgunits.name order by count(orgunits.name) desc limit 1;
 
 
-- Q6: ...

create or replace function Q6(integer) returns text
as
$$
	select people.name from people where people.unswid = $1 or people.id = $1;

$$ language sql
;


-- Q7: ...

create or replace function Q7(text)
	returns table (course text, year integer, term text, convenor text)
as $$
	
	select $1 as course,year, term::text, people.name from people 
	join course_staff on people.id = course_staff.staff
	join courses on course_staff.course = courses.id 
	join semesters on courses.semester = semesters.id 
	join subjects on courses.subject = subjects.id
	join staff_roles on staff_roles.id = course_staff.role 
	where staff_roles.name = 'Course Convenor' and subjects.code = $1;
	
$$ language sql
;


-- Q8: ...

/* This was obtained from the transcript(integer) function */

create or replace function Q8(integer)
	returns setof NewTranscriptRecord
as $$
declare
	rec NewTranscriptRecord;
	UOCtotal integer := 0;
	UOCpassed integer := 0;
	wsum integer := 0;
	wam integer := 0;
	x integer;
	
begin
	 select s.id into x
        from   Students s join People p on (s.id = p.id)
        where  p.unswid = $1;
        if (not found) then
                raise EXCEPTION 'Invalid student %',$1;
        end if;
		for rec in
                select su.code,
                         substr(t.year::text,3,2)||lower(t.term),
						 min(programs.code),
                         substr(su.name,1,20),
                         e.mark, e.grade, su.uoc
                from   People p
						 join program_enrolments on program_enrolments.student = p.id
						 join programs on program_enrolments.program = programs.id
                         join Students s on (p.id = s.id)
                         join Course_enrolments e on (e.student = s.id)
                         join Courses c on (c.id = e.course)
                         join Subjects su on (c.subject = su.id)
                         join Semesters t on (c.semester = t.id)

                where  p.unswid = $1
                group by t.starting, su.code, t.year, t.term, programs.code, su.name, e.mark, e.grade, su.uoc order by t.starting, su.code
				
        loop
				if (rec.grade = 'SY') then
                        UOCpassed := UOCpassed + rec.uoc;
                elsif (rec.mark is not null) then
                        if (rec.grade in ('PT','PC','PS','CR','DN','HD','A','B','C')) then
                                UOCpassed := UOCpassed + rec.uoc;
                        end if;
                        UOCtotal := UOCtotal + rec.uoc;
                        wsum := wsum + (rec.mark * rec.uoc);
                        if (rec.grade not in ('PT','PC','PS','CR','DN','HD','A','B','C')) then
                                rec.uoc := 0;
                        end if;
				end if;
                return next rec;
        end loop;
        if (UOCtotal = 0) then
                rec := (null,null,null,'No WAM available',null,null,null);
        else
                wam := wsum / UOCtotal;
                rec := (null,null,null,'Overall WAM',wam,null,UOCpassed);
        end if;
        return next rec;
		
end;
$$ language plpgsql
;


-- Q9: ...

create or replace function Q9(integer)
	returns setof AcObjRecord
as $$
declare
	rec AcObjRecord;
	id integer := 0;
	name text := null;
	gtype text := null;
	glogic text := null;
	gdefby text := null;
	negated char(1) := null;
	parent integer := 0;
	definition text := null;
    objects text := null;
	
begin
	select  acad_object_groups.id, acad_object_groups.name, acad_object_groups.gtype, acad_object_groups.glogic, acad_object_groups.gdefby, acad_object_groups.negated, acad_object_groups.parent, acad_object_groups.definition
	into id, name, gtype, glogic, gdefby, negated, parent, definition from acad_object_groups 
	where acad_object_groups.gdefby = 'pattern' 
	and acad_object_groups.id = $1
	and acad_object_groups.definition not like '%;%' 
	and acad_object_groups.definition not like '%/%'; 
	
	if definition like 'FREE%' then
	rec := (gtype, definition);
	return next rec;
	elsif definition like 'GENG%' then
	rec := (gtype, definition);	
	return next rec;
	elsif definition like 'GEN%' then
	rec := (gtype, definition);	
	return next rec;
	elsif definition like 'ZGEN%' then
	rec := (gtype, definition);	
	return next rec;
	else
	for objects in
	select distinct subjects.code from subjects			
	where subjects.code ~* (select replace(replace(definition, ',', '|'), '#', '.{1}')) order by subjects.code
	loop
	rec := (gtype, objects);
	return next rec;
	end loop;
	end if;
end;
$$ language plpgsql
;



