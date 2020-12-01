local dpl = {}
local php

--[[
@brief Setup the interface.
--]]

function dpl.setupInterface( options )
   -- Remove setup function
   dpl.setupInterface = nil
   
   -- Copy the PHP callbacks to a local variable, and remove the global
   php = mw_interface
   mw_interface = nil
   
   -- Do any other setup here
   dpl.getFullpagenames = php.getFullpagenames
   dpl.getPagenames = php.getPagenames
   dpl.getPages = php.getPages

   -- Install into the mw global
   mw = mw or {}
   mw.ext = mw.ext or {}
   mw.ext.dpl = dpl
   
   -- Indicate that we're loaded
   package.loaded['mw.ext.dpl'] = dpl
end

return dpl
