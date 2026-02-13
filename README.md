# Course bulk actions

This plugin allows you to do bulk actions on courses based on your own searches.

You can save the searches for later use (give your search a title to save it), or just use the search as-is.

When you see your search results you can select the ones you want to do an operation on.

Show/Hide will work instantly on your choices.

Deleting courses works a little differently. Because deleting courses can take a little while, the courses you select for deletion
are "queued" for a "grace period". This grace period helps to prevent accidental deletion. By default this is 7 days.

Whilst a course is waiting for the grace period to end, you can "Dequeue" it if you realise that you don't want it saved.

For auditing purposes a log is retained of the deletions that have occurred.

## Can I recover a deleted course?

This depends on whether you have enabled the Category recycle bin. Site administration -> Plugins -> Admin tools -> Recycle bin.

If the Category recycle bin is enabled, then a back up of your course will be made and retained for the time specified.

If the Category recycle bin is not enabled, then no back up is made, and your course is immediately deleted.

To recover a deleted course go to the course category the course was deleted from and select the course to restore.

## Disk space

If you have enable the Category recycle bin, be aware that this will impact your moodledata disk space. If you are doing lots of
deletes and/or keeping the deleted courses for a long time, this will increase the amount of disk space you will need.

Of course, the amount of space required is dependent on your default back up settings and the size of the course.

Best to start slow and see how that goes.

## Course bulk actions cron task

This is a scheduled task that runs out of hours (every 15 minutes between 2000 and 0400). The task looks for queued items that have
passed the Grace period and can be deleted. The task then spins out an adhoc task for each course (Delete course task).

The Delete course task will run whenever Moodle is ready to run it.

## Logs

The Logs tab will show the status of any course after being Queued.

- Pending: The Adhoc task is waiting to be processed
- Processing: The Adhoc tasks is currently underway
- Completed: The course has been successfully deleted
- Failed: For whatever reason the course has not been deleted (though some parts may have been deleted/removed e.g. enrolments).

## Failed deletion

In the case of failed deletion check out Moodle's task logs. All the output from the deletion is stored in those logs.

`Site administration -> Server -> Tasks -> Task logs` then filter on "Delete course task (tool_coursebulkactions)"