(function ($) {
  'use strict';

  // sidebar submenu collapsible js
  $(".sidebar-menu .dropdown").on("click", function(){
    var item = $(this);
    item.siblings(".dropdown").children(".sidebar-submenu").slideUp();

    item.siblings(".dropdown").removeClass("dropdown-open");

    item.siblings(".dropdown").removeClass("open");

    item.children(".sidebar-submenu").slideToggle();

    item.toggleClass("dropdown-open");
  });

  function updateSidebarToggleState($btn) {
    if (!$btn.length) return;

    const isMobile = window.matchMedia("(max-width: 1199px)").matches;
    const isCollapsed = $btn.hasClass("active");
    const isMobileOpen = $(".sidebar").hasClass("sidebar-open");
    const icon = $btn.find(".sidebar-toggle-icon")[0];

    if (icon) {
      if (isMobile) {
        icon.setAttribute(
          "icon",
          isMobileOpen ? "radix-icons:cross-2" : "solar:round-alt-arrow-right-linear"
        );
      } else {
        icon.setAttribute(
          "icon",
          isCollapsed ? "solar:round-alt-arrow-right-linear" : "solar:round-alt-arrow-left-linear"
        );
      }
    }

    let label = "Collapse sidebar";
    if (isMobile) {
      label = isMobileOpen ? "Close menu" : "Open menu";
    } else if (isCollapsed) {
      label = "Expand sidebar";
    }

    $btn.attr("aria-label", label);
    $btn.attr("title", label);
    $btn.attr("aria-expanded", isMobile ? (isMobileOpen ? "true" : "false") : (isCollapsed ? "false" : "true"));
  }

  $(".sidebar-toggle").on("click", function(){
    const isMobile = window.matchMedia("(max-width: 1199px)").matches;

    if (isMobile) {
      $(".sidebar").toggleClass("sidebar-open");
      $("body").toggleClass("overlay-active");
    } else {
      $(this).toggleClass("active");
      $(".sidebar").toggleClass("active");
      $(".dashboard-main").toggleClass("active");
    }

    updateSidebarToggleState($(this));
  });

  $(".sidebar-close-btn").on("click", function(){
    $(".sidebar").removeClass("sidebar-open");
    $("body").removeClass("overlay-active");
    updateSidebarToggleState($(".sidebar-toggle"));
  });

  $(window).on("resize", function () {
    updateSidebarToggleState($(".sidebar-toggle"));
  });

  updateSidebarToggleState($(".sidebar-toggle"));

  //to keep the current page active
  $(function () {
    for (
      var nk = window.location,
        o = $("ul#sidebar-menu a")
          .filter(function () {
            return this.href == nk;
          })
          .addClass("active-page") // anchor
          .parent()
          .addClass("active-page");
      ;

    ) {
      // li
      if (!o.is("li")) break;
      o = o.parent().addClass("show").parent().addClass("open");
    }
  });

/**
* Utility function to calculate the current theme setting based on localStorage.
*/
function calculateSettingAsThemeString({ localStorageTheme }) {
  if (localStorageTheme !== null) {
    return localStorageTheme;
  }
  return "light"; // default to light theme if nothing is stored
}

/**
* Utility function to update the button text and aria-label.
*/
function updateButton({ buttonEl, isDark }) {
  const newCta = isDark ? "Switch to light theme" : "Switch to dark theme";
  buttonEl.setAttribute("aria-label", newCta);
  buttonEl.setAttribute("title", newCta);

  const icon = buttonEl.querySelector(".crm-theme-icon");
  if (icon) {
    icon.setAttribute("icon", isDark ? "solar:moon-linear" : "solar:sun-linear");
  }
}

/**
* Utility function to update the theme setting on the html tag.
*/
function updateThemeOnHtmlEl({ theme }) {
  document.querySelector("html").setAttribute("data-theme", theme);
}

/**
* 1. Grab what we need from the DOM and system settings on page load.
*/
const button = document.querySelector("[data-theme-toggle]");
const localStorageTheme = localStorage.getItem("theme");

/**
* 2. Work out the current site settings.
*/
let currentThemeSetting = calculateSettingAsThemeString({ localStorageTheme });

/**
* 3. If the button exists, update the theme setting and button text according to current settings.
*/
if (button) {
  updateButton({ buttonEl: button, isDark: currentThemeSetting === "dark" });
  updateThemeOnHtmlEl({ theme: currentThemeSetting });

  /**
  * 4. Add an event listener to toggle the theme.
  */
  button.addEventListener("click", (event) => {
    const newTheme = currentThemeSetting === "dark" ? "light" : "dark";

    localStorage.setItem("theme", newTheme);
    updateButton({ buttonEl: button, isDark: newTheme === "dark" });
    updateThemeOnHtmlEl({ theme: newTheme });

    currentThemeSetting = newTheme;
  });
} else {
  // If no button is found, just apply the current theme to the page
  updateThemeOnHtmlEl({ theme: currentThemeSetting });
}


// =========================== Table Header Checkbox checked all js Start ================================
$('#selectAll').on('change', function () {
  $('.form-check .form-check-input').prop('checked', $(this).prop('checked')); 
}); 

  // Remove Table Tr when click on remove btn start
  $('.remove-btn').on('click', function () {
    $(this).closest('tr').remove(); 

    // Check if the table has no rows left
    if ($('.table tbody tr').length === 0) {
      $('.table').addClass('bg-danger');

      // Show notification
      $('.no-items-found').show();
    }
  });
  // Remove Table Tr when click on remove btn end
})(jQuery);