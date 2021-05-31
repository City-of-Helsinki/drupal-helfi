ifeq ($(RUN_ON), docker)
	PATH_TO_PLATFORM_CONFIG := ./modules/contrib/helfi_platform_config/translations
else
	PATH_TO_PLATFORM_CONFIG := ./public/modules/contrib/helfi_platform_config/translations
endif

PHONY += drush-locale-export
drush-locale-export: ## Export locale PO files
	$(call step,Export locale PO files...)
	$(call step,Handle Finnish...)
	$(call drush_on_${RUN_ON},locale:export fi --types=customized > ${PATH_TO_PLATFORM_CONFIG}/fi/strings.po)
	$(call step,Handle Swedish...)
	$(call drush_on_${RUN_ON},locale:export sv --types=customized > ${PATH_TO_PLATFORM_CONFIG}/sv/strings.po)
	$(call step,Handle Russian...)
	$(call drush_on_${RUN_ON},locale:export ru --types=customized > ${PATH_TO_PLATFORM_CONFIG}/ru/strings.po)
	$(call step,All files exported to ${PATH_TO_PLATFORM_CONFIG})

PHONY += drush-locale-export-not-translated
drush-locale-export-not-translated: ## Export locale PO files
	$(call step,Export all untranslated translations...)
	$(call step,Handle Finnish...)
	$(call drush_on_${RUN_ON},locale:export fi --types=not-translated > ${PATH_TO_PLATFORM_CONFIG}/fi/not-translated.po.tmp)
	$(call step,Handle Swedish...)
	$(call drush_on_${RUN_ON},locale:export sv --types=not-translated > ${PATH_TO_PLATFORM_CONFIG}/sv/not-translated.po.tmp)
	$(call step,Handle Russian...)
	$(call drush_on_${RUN_ON},locale:export ru --types=not-translated > ${PATH_TO_PLATFORM_CONFIG}/ru/not-translated.po.tmp)
	$(call step,All files exported to ${PATH_TO_PLATFORM_CONFIG})
