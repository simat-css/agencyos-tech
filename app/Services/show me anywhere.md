show me anywhere
 
in 10 days aprox- how much you did without robotic feature/command base functionality....?? anywhere you implement robotic show me
 
you wasted alot time if you didn't put any time on that main feature, seriously. You disappoint me. I was very excited for this meeting today you assure me for that delivery by this weekend, how i passed this time for you only i knew my efforts.
 
Nikita Mathur things not going positively.
 
sorry sir ,but i think robotic  feature is only for  user creation and its in next module .
 
I told you everything, i'll reconnect with her to cross check everything.
 
Simat Koundal 10 days alot you took whatever you showed me, this was not huge work if we are using AI as coding help
 
that you knows verywell.
 
Tell me one thing - Robotic feature atleast on one phase like user or company - when you can show me?
 
also whatever you created yet, make a video and send to the group using wetransfer




//testing company:
create company named ABC Technologies with email abc@gmail.com phone 9876543210 address Delhi
show company ABC
show company ABC
show active companies
show inactive companies
show company statistics
company statistics
update company ABC Technologies email newabc@gmail.com
->phir confirm
:-update company ABC Technologies phone 9999999999
->confirm
:-deactivate company ABC Technologies
->confirm
activate company ABC Technologies
:-delete company ABC Technologies
->confirm
:-show latest notifications

Nagative testing:-
create company named XYZ with email abc@gmail.com phone 9876543211 address Noida
:-invalid mail testing
update company ABC Technologies email abc
:-not found
show company TestCompany
:-No confirm
:-No pending action found.
 

 Department-:
 create department named Human Resources in company AgencyOS with code HR001
 create department named Information Technology in company AgencyOS with code IT001
 create department named Finance in company AgencyOS with code FIN001

 update department Human Resources in company AgencyOS name HR Department
 confirm
 update department HR Department in company AgencyOS code HR100
 confirm
 update department HR Department in company AgencyOS description Handles employee management and recruitment
 confirm
 update department HR Department in company AgencyOS name Human Capital code HC001 description Human resource operations department
 confirm
 show department Human Capital in company AgencyOS
 search department Human
 search department IT in company AgencyOS
 list departments in company AgencyOS
 list department in company AgencyOS
 activate department Human Capital in company AgencyOS
 deactivate department Human Capital in company AgencyOS
 delete department Human Capital from company AgencyOS
 Permission Validation Tests

Aise user se test karo jiske paas permission na ho:

create department named Testing in company AgencyOS with code TEST001
update department Finance in company AgencyOS name Finance Team
delete department Finance from company AgencyOS
activate department Finance in company AgencyOS
Validation Tests
Duplicate Code
create department named New Finance in company AgencyOS with code FIN001

Expected:

Department code already exists.
Duplicate Name
create department named Finance in company AgencyOS with code FIN999

Expected:

Department name already exists in this company.
Company Not Found
create department named HR in company XYZCompany with code HR001

Expected:

Company not found.
Department Not Found
show department UnknownDepartment in company AgencyOS

Expected:

Department not found.
Confirm Without Pending Action
confirm

Expected:

No pending department action found.