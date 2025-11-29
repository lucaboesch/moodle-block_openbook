@block @block_openbook
Feature: Tests for teacher files in Openbook resource folder

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | 1        | teacher1@asd.com |
      | student1 | Student   | 1        | student1@asd.com |
      | student2 | Student   | 1        | student1@asd.com |
    And the following "courses" exist:
      | fullname | shortname | category | startdate     |
      | Course 1 | C1        | 0        | ##yesterday## |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
    And the following "activities" exist:
      | activity | course | name                       | maxbytes | filesarepersonal |
      | openbook | C1     | Openbook resource folder 1 | 8388608  | 0                |
      | openbook | C1     | Openbook resource folder 2 | 8388608  | 0                |
    Given the following "activities" exist:
      | activity | course | name   | idnumber | showblocks |
      | quiz     | C1     | Quiz 1 | q1       | 1          |
      | quiz     | C1     | Quiz 2 | q2       | 1          |
    And the following "question categories" exist:
      | contextlevel    | reference | name                     |
      | Activity module | q1        | Test questions of Quiz 1 |
      | Activity module | q2        | Test questions of Quiz 2 |
    And the following "questions" exist:
      | questioncategory         | qtype     | name |
      | Test questions of Quiz 1 | truefalse | TF1  |
      | Test questions of Quiz 2 | truefalse | TF2  |
    And quiz "Quiz 1" contains the following questions:
      | question | page |
      | TF1      | 1    |
    And quiz "Quiz 2" contains the following questions:
      | question | page |
      | TF2      | 1    |

  @javascript @_file_upload
  Scenario: Upload two files as lecturer in two openbook instances add two openbook blocks in two quizzes
    When I am on the "Openbook resource folder 1" "openbook activity" page logged in as teacher1
    And I should see "Own files"
    And I follow "Edit/upload teacher files"
    And I should see "Teacher files that are visible to everybody"
    And I upload "blocks/openbook/tests/fixtures/teacher_file_1.pdf" file to "Teacher files that are visible to everybody" filemanager
    And I press "Save changes"
    And I should see "teacher_file_1.pdf"
    And I am on "Course 1" course homepage with editing mode on
    And I am on the "Quiz 1" "mod_quiz > View" page
    And the add block selector should contain "Openbook resource folder files..." block
    And the following "blocks" exist:
      | blockname | contextlevel    | reference | pagetypepattern | defaultregion | title                          | openbook                   |
      | openbook  | Activity module | q1        | mod-quiz-*      | side-pre      | Openbook resource folder files | Openbook resource folder 1 |
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I configure the "Openbook resource folder files" block
    And I set the field "Take files from this Openbook resource folder" to "Openbook resource folder 1"
    And I press "Save changes"
    And I should see "Openbook resource folder files"
    And I should see "Openbook resource folder 1"
    And I am on the "Openbook resource folder 2" "openbook activity" page logged in as teacher1
    And I should see "Own files"
    And I follow "Edit/upload teacher files"
    And I should see "Teacher files that are visible to everybody"
    And I upload "blocks/openbook/tests/fixtures/teacher_file_2.pdf" file to "Teacher files that are visible to everybody" filemanager
    And I press "Save changes"
    And I should see "teacher_file_2.pdf"
    And I am on "Course 1" course homepage with editing mode on
    And I am on the "Quiz 2" "mod_quiz > View" page
    And the add block selector should contain "Openbook resource folder files..." block
    And the following "blocks" exist:
      | blockname | contextlevel    | reference | pagetypepattern | defaultregion | title                          | openbook                   |
      | openbook  | Activity module | q2        | mod-quiz-*      | side-pre      | Openbook resource folder files | Openbook resource folder 2 |
    And I am on the "Quiz 2" "mod_quiz > View" page
    And I configure the "Openbook resource folder files" block
    And I set the field "Take files from this Openbook resource folder" to "Openbook resource folder 2"
    And I press "Save changes"
    And I should see "Openbook resource folder files"
    And I should see "Openbook resource folder 2"
    When I am on the "Quiz 1" "quiz activity" page logged in as student1
    And I press "Attempt quiz"
    Then "Openbook resource folder files" "block" should exist
    And I should see "Access is granted to the Openbook resource folder 1 Openbook resource folder containing these files:"
    And I should see "teacher_file_1.pdf"
    But I should not see "teacher_file_2.pdf"
    And I am on the "Quiz 2" "quiz activity" page logged in as student1
    And I press "Attempt quiz"
    Then "Openbook resource folder files" "block" should exist
    And I should see "Access is granted to the Openbook resource folder 2 Openbook resource folder containing these files:"
    And I should see "teacher_file_2.pdf"
    But I should not see "teacher_file_1.pdf"
