ifeq ($(RUN_ON), docker)
	PATH_TO_PLATFORM_CONFIG := ./modules/custom/helfi_platform_config/translations
else
	PATH_TO_PLATFORM_CONFIG := ./public/modules/custom/helfi_platform_config/translations
endif

comma:=,

PHONY += drush-locale-export
drush-locale-export: ## Export locale PO files
	$(call step,Export locale PO files...)
	$(call step,Handle Finnish...)
	$(call drush_on_${RUN_ON},locale:export fi --types=customized${comma}not-translated > ${PATH_TO_PLATFORM_CONFIG}/fi/strings.po)
	$(call step,Handle Swedish...)
	$(call drush_on_${RUN_ON},locale:export sv --types=customized${comma}not-translated > ${PATH_TO_PLATFORM_CONFIG}/sv/strings.po)
	$(call step,Handle Russian...)
	$(call drush_on_${RUN_ON},locale:export ru --types=customized${comma}not-translated > ${PATH_TO_PLATFORM_CONFIG}/ru/strings.po)
	$(call step,All files exported to ${PATH_TO_PLATFORM_CONFIG})
