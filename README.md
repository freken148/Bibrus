# Bibrus
Shitty piece of code that should be **brus ripoff 

# Features
- Lesson plan
- Grades
- Attendance

# To do
- [ ] Complete `terminarz.php`
- [ ] Add regexp to login fields
- [ ] Add student journal number
- [ ] Fix bugs (it's not really bugs but rather unfinished work)
<<<<<<< HEAD
- [ ] Completly rewrite `plan_lekcji_functions.php`
- [ ] Add superadmin role with rights to change everything
=======
- [ ] Completly rewrite "Plan lekcji"
- [ ] Add flag system to Schedule to make possible add things like "Lekcja odwołana", "Zastępstwo"
>>>>>>> 2537edaa633ba92d612914af642a74d0173bac83
- [ ] Whatever else that comes to my mind and be worth spent time

# Known bugs
- Error when using `\` char in input fields into `logowanie.php`
- "Zwolniony" shouldn't impact on attendance at all but it does as positive

# Advice
It's better to use `127.0.0.1` instead of `localhost` for avoid problems when Windows firstly read `localhost` as IPv6 `::1` address what causes a very big slowdown a work of xampp and all that related to it. 
Also run xampp as administrator whenever possible to avoid errors on close.

# generate.php
Also there is `generate.php` which purely vibecoded and used to generate data into tables, to use it put into browser address bar like `127.0.0.1/folder/core/generate.php` and wait until it done (estimated time: 30 sec)

