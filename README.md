# Bibrus
Shitty piece of code that should be **brus ripoff 

# Features
- Lesson plan
- Grades
- Attendance tracking
- Interactive timetable

# To do
- [ ] Complete `terminarz.php`
- - [x] Calendar
- - [x] Fetching data from db and show to end user
- - [x] Details view, when user click on the event
- - [ ] Possibilty to add/remove events for teachers 
- [ ] Changes in `plan_lekcji.php` when `Zastępstwo` or `Odwołanie` and teacher absent without replacement
- [x] Add regexp to login fields
- [x] Add student journal number
- [ ] Fix bugs (it's not really bugs but rather unfinished work)
- [x] Completly rewrite `plan_lekcji_functions.php`
- [ ] Add superadmin role with rights to change everything
- [ ] Changing grades after rating
- [ ] Login for students
- [ ] Adding classes to tags for future work on appearence 
- [ ] (maybe) Rewrite everything to more OOP way (now it's rather mix of procedural and OOP solutions, mainly procedural)
- [ ] (maybe) Export all queries to separate file where it's been procedures which called by EXEC instead of whole query
- [ ] Whatever else that comes to my mind and be worth spent time

# Known bugs
- ~~Error when using `\` char in input fields into `logowanie.php`~~
- `Zwolniony` shouldn't impact on attendance at all but it does as positive

# Advice
It's better to use `127.0.0.1` instead of `localhost` for avoid problems when Windows firstly read `localhost` as IPv6 `::1` address what causes a very big slowdown of xampp and all that related to it. 
Also run xampp as administrator whenever possible to avoid errors on close.

# generate.php
Also there is `generate.php` which purely vibecoded and used to generate data into tables, to use it put into browser address bar like `127.0.0.1/folder/core/generate.php` and wait until it done (estimated time: 5 sec)