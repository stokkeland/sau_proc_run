<?php
 /**
  * Run a system process - capture stdout and stderr and result, supply stdin (Optional)
  *
  * System Requirements:
  * Should work on PHP 5 i think, but you should not be using that, so lets say PHP >7.2
  *
  * This is almost ouf ot the PHP manual, I just abstracted it to a simpler
  * supply the command, and optional string for stdin, and then the result will
  * come in an array, that should contain:
  *   array(
  *     ['stdout'] => 'The data returned from app',
  *     ['result'] => 0, // or non-zero if error occured
  *     ['stderr'] => 'only if there was any error output'
  *   )
  * This function is not failsafe - the command probably needs to be shell sane, escaped etc
  * I do a lot of sysadmin stuff - so I use all this for shell scripts and automation.
  * Do NOT take user input via web or forms and use in this command, that be bad security.
  *
  *   Example:
  *      user@host:~$ php -r 'include("sau_proc_run.php"); $ps=sau_proc_run("ps -ef"); var_dump($ps);'
  *   Output (truncated):
  *    array(2) {
  *      ["result"]=>
  *      int(0)
  *      ["stdout"]=>
  *      string(21412) "UID        PID  PPID  C STIME TTY          TIME CMD
  *    root         1     0  0  2012 ?        00:00:06 init [3]
  *    root         2     1  0  2012 ?        00:00:09 [migration/0]
  *     ...
  *    mailnil 22598  7775  0 09:15 ?        00:00:04 MailScanner: waiting for messages
  *    "
  *    }
  *
  * TODO for your implementation: Fix Exception/error handling, replace die()'s
  */

    function sau_proc_run ($cmd,$stdin='')
    {
        $r = array('result'=>32767); // default err code if no result. Why? just because.
        $stderr = '';
        $tmp = tmpfile(); // create a system temp file
        if (!is_resource($tmp)) die('Failed to create tmp file for proc_run');

        $descriptorspec = array(
           0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
           1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
           2 => $tmp                 // stderr is a file to write to
        );

        $process = proc_open($cmd, $descriptorspec, $pipes );  // Fire away

        if (is_resource($process))    // if it is sane
        {
          if ($stdin) fwrite($pipes[0], $stdin);  // send stdin if we got any
          fclose($pipes[0]);              // close stdin, so that process can finish

          $r['stdout'] = stream_get_contents($pipes[1]); // get stdout
          fclose($pipes[1]);            // finish off

          $r['result'] = proc_close($process);    // close the process and get exit code

        }
        else die('Failed to create a process in proc_run');

        fseek($tmp,0);    // jump to the beginning of the tmp file
        while (!feof($tmp)) { $stderr .= fread($tmp, 8192); }  //get the content
        fclose($tmp);    // close and delete the tmp file
        if ($stderr) $r['stderr'] = $stderr;  // if any, add it to the result array
        return $r;
  }
