function refresh()
{
 parent.log.location.href="keylogger.txt";
 setTimeout("refresh()",1000);
}
refresh();
