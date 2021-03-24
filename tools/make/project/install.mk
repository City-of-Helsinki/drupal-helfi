ifeq ($(DRUPAL_CONF_EXISTS)$(DRUPAL_VERSION),no8)
    DRUPAL_NEW_TARGETS := up build drush-si drush-enable-modules drush-locale-import drush-uli
    DRUPAL_FRESH_TARGETS := up build sync post-install drush-locale-import
endif

PHONY += drush-enable-modules
drush-enable-modules: ## Enable modules and base configurations.
	$(call step,Install base configurations...)
	$(call drush_on_docker,en -y helfi_platform_config helfi_base_config)

PHONY += drush-locale-import
drush-locale-import: ## Import locale PO files
	$(call step,Import locale PO files...)
	$(call drush_on_${RUN_ON},helfi:locale-import helfi_platform_config)
