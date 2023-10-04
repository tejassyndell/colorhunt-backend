@echo off
:val
set /p loopcount=Number of copies:
echo %loopcount%|findstr /xr "[1-9][0-9]* 0" >nul && (
  
  IF "%loopcount%" == "0" (
	::echo Shoud be at least 1 number
	echo must add at least 1 number
	goto val 
  )
) || (
  echo '%loopcount%' is not a valid number
  ::echo This entry can only contain numbers
  goto val
)


:loop
COPY /B C:\Users\abc\Desktop\prnfile\Output.prn \\ExportDispatched-PC\POSTEK-EM-Series-(203-dpi)
set /a loopcount=loopcount-1
if %loopcount%==0 goto exitloop
goto loop
:exitloop

pause
exit