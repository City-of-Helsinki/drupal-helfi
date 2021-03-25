ifeq ($(RUN_ON), docker)
	PATH_TO_PLATFORM_CONFIG := ./modules/custom/helfi_platform_config/translations
else
	PATH_TO_PLATFORM_CONFIG := ./public/modules/custom/helfi_platform_config/translations
endif

PHONY += drush-locale-export
drush-locale-export: ## Export locale PO files
	$(call step,Export locale PO files...)
	$(call drush_on_${RUN_ON},locale-export fi > ${PATH_TO_PLATFORM_CONFIG}/fi/strings.po)
	$(call drush_on_${RUN_ON},locale-export sv > ${PATH_TO_PLATFORM_CONFIG}/sv/strings.po)
	$(call drush_on_${RUN_ON},locale-export ru > ${PATH_TO_PLATFORM_CONFIG}/ru/strings.po)
