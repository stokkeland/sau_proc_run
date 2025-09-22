# sau_proc_run - PHP System Process Runner

A simple PHP function for executing system commands with full control over stdin, stdout, and stderr streams.

## Overview

`sau_proc_run` is a lightweight wrapper around PHP's `proc_open()` function that simplifies the execution of system processes. It captures both standard output and error output, returns the process exit code, and optionally allows you to supply input to the process via stdin.

## Author & Version

- **Author**: Thomas Stokkeland with help from PHP.net Manual
- **Last Revision**: 2024-02-27

## System Requirements

- **PHP Version**: 7.2 or higher
- **Operating System**: Unix-like systems (Linux, macOS, etc.)
- **Extensions**: Standard PHP installation with process control functions

## Function Signature

```php
function sau_proc_run($cmd, $stdin = '')
```

### Parameters

| Parameter | Type   | Required | Description |
|-----------|--------|----------|-------------|
| `$cmd`    | string | Yes      | The shell command to execute |
| `$stdin`  | string | No       | Optional input to send to the process via stdin |

### Return Value

Returns an associative array with the following structure:

```php
array(
    'stdout' => 'The data returned from the application',
    'result' => 0,  // Exit code: 0 for success, non-zero for errors
    'stderr' => 'Error output (only present if there were errors)'
)
```

| Key      | Type    | Description |
|----------|---------|-------------|
| `stdout` | string  | Standard output from the executed command |
| `result` | integer | Process exit code (0 = success, non-zero = error) |
| `stderr` | string  | Standard error output (only included if errors occurred) |

## Usage Examples

### Basic Command Execution

```php
<?php
include("sau_proc_run.php");

// Execute a simple command
$result = sau_proc_run("ps -ef");
var_dump($result);
```

### Command with Input

```php
<?php
include("sau_proc_run.php");

// Send input to a command via stdin
$input = "Hello World\n";
$result = sau_proc_run("cat", $input);
echo $result['stdout']; // Outputs: Hello World
```

### Error Handling Example

```php
<?php
include("sau_proc_run.php");

// Execute a command that might fail
$result = sau_proc_run("ls /nonexistent/directory");

if ($result['result'] !== 0) {
    echo "Command failed with exit code: " . $result['result'] . "\n";
    if (isset($result['stderr'])) {
        echo "Error output: " . $result['stderr'] . "\n";
    }
} else {
    echo "Success: " . $result['stdout'] . "\n";
}
```

### Command Line Usage

You can also run this directly from the command line:

```bash
php -r 'include("sau_proc_run.php"); $ps=sau_proc_run("ps -ef"); var_dump($ps);'
```

## Security Considerations

⚠️ **Important Security Warning**

This function is **NOT** safe for handling user input from web forms or untrusted sources. The command parameter is passed directly to the shell without sanitization.

### Safe Usage Guidelines

- **Never** pass user input directly to the `$cmd` parameter
- Always validate and sanitize commands before execution
- Use in controlled environments (system administration scripts, CLI tools)
- Consider using `escapeshellarg()` and `escapeshellcmd()` for dynamic commands

### Unsafe Examples (Don't Do This)

```php
// DANGEROUS - Never do this!
$userInput = $_POST['command'];
$result = sau_proc_run($userInput);
```

### Safe Examples

```php
// Safe - predefined commands only
$allowedCommands = ['ps -ef', 'df -h', 'uptime'];
$command = $allowedCommands[0];
$result = sau_proc_run($command);

// Safe - properly escaped dynamic parameters
$filename = escapeshellarg($userProvidedFilename);
$command = "ls -la " . $filename;
$result = sau_proc_run($command);
```

## Error Handling

The function uses `die()` statements for critical errors, which will terminate the script. In production environments, you should consider implementing proper exception handling:

### Current Error Conditions

1. **Temporary file creation failure**: Dies with "Failed to create tmp file for proc_run"
2. **Process creation failure**: Dies with "Failed to create a process in proc_run"

### Recommended Improvements

For production use, consider modifying the function to throw exceptions instead of using `die()`:

```php
// Example improvement (not included in original function)
if (!is_resource($tmp)) {
    throw new RuntimeException('Failed to create temporary file for process execution');
}
```

## How It Works

1. **Creates a temporary file** for capturing stderr output
2. **Sets up pipe descriptors** for stdin, stdout, and stderr
3. **Launches the process** using `proc_open()`
4. **Sends optional stdin data** to the process
5. **Captures stdout** from the process
6. **Waits for process completion** and gets exit code
7. **Reads stderr** from the temporary file
8. **Returns structured result** array

## Limitations

- Commands must be shell-safe and properly escaped
- No built-in timeout mechanism
- Uses `die()` for error handling (not exception-based)
- Temporary file creation required for stderr capture
- No async execution support

## Common Use Cases

- **System Administration**: Running maintenance scripts, checking system status
- **Automation**: Batch processing, file operations, system monitoring
- **CLI Tools**: Building PHP command-line utilities
- **Integration**: Interfacing with system tools and utilities

## Troubleshooting

### Process Fails to Start
- Check that the command exists and is executable
- Verify path accessibility
- Ensure proper permissions

### No Output Captured
- Check if the command writes to stdout vs stderr
- Verify the command actually produces output
- Check exit codes for errors

### Memory Issues
- Large outputs may consume significant memory
- Consider streaming for very large outputs
- Monitor memory usage with large processes

