@mod @mod_subsection
Feature: The module menu replaces the section menu when accessing the subsection page
  In order to use subsections
  As an teacher
  I need to see the module action menu in the section page.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category | numsections |
      | Course 1 | C1        | 0        | 2           |
    And the following "activity" exists:
      | activity | subsection  |
      | name     | Subsection1 |
      | course   | C1          |
      | idnumber | subsection1 |
      | section  | 1           |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And I am on the "C1" "Course" page logged in as "teacher1"

  @javascript
  Scenario: The action menu for subsection meets the module menu
    Given I click on "Subsection1" "link" in the "region-main" "region"
    And I turn editing mode on
    # Open the action menu.
    When I click on "Edit" "icon" in the "[data-region='header-actions-container']" "css_element"
    Then I should see "Move right"
    And I should see "Assign roles"
    But I should not see "Highlight"
