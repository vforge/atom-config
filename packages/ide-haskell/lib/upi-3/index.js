"use strict";
function __export(m) {
    for (var p in m) if (!exports.hasOwnProperty(p)) exports[p] = m[p];
}
Object.defineProperty(exports, "__esModule", { value: true });
const atom_1 = require("atom");
const utils_1 = require("../utils");
__export(require("./instance"));
function consume(pluginManager, options) {
    const { name, menu, messageTypes, events, controls, params, tooltip } = options;
    const disp = new atom_1.CompositeDisposable();
    if (menu) {
        const menuDisp = atom.menu.add([{
                label: utils_1.MAIN_MENU_LABEL,
                submenu: [{ label: menu.label, submenu: menu.menu }],
            }]);
        disp.add(menuDisp);
    }
    if (messageTypes) {
        for (const type of Object.keys(messageTypes)) {
            const opts = messageTypes[type];
            pluginManager.outputPanel.createTab(type, opts);
        }
    }
    if (events) {
        for (const k in events) {
            if (k.startsWith('on') && pluginManager[k]) {
                let v = events[k];
                if (!Array.isArray(v)) {
                    v = [v];
                }
                for (const i of v) {
                    disp.add(pluginManager[k](i));
                }
            }
        }
    }
    if (tooltip) {
        let handler;
        let priority;
        let eventTypes;
        if (typeof tooltip === 'function') {
            handler = tooltip;
        }
        else {
            ({ handler, priority, eventTypes } = tooltip);
        }
        if (!priority) {
            priority = 100;
        }
        disp.add(pluginManager.tooltipRegistry.register(name, { priority, handler, eventTypes }));
    }
    if (controls) {
        for (const i of controls) {
            disp.add(pluginManager.outputPanel.addPanelControl(i));
        }
    }
    if (params) {
        for (const paramName of Object.keys(params)) {
            const spec = params[paramName];
            disp.add(pluginManager.configParamManager.add(name, paramName, spec));
        }
    }
    return disp;
}
exports.consume = consume;
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaW5kZXguanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi9zcmMvdXBpLTMvaW5kZXgudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6Ijs7Ozs7QUFBQSwrQkFBc0Q7QUFHdEQsb0NBQTBDO0FBRTFDLGdDQUEwQjtBQUUxQixpQkFBd0IsYUFBNEIsRUFBRSxPQUFpQztJQUNyRixNQUFNLEVBQUUsSUFBSSxFQUFFLElBQUksRUFBRSxZQUFZLEVBQUUsTUFBTSxFQUFFLFFBQVEsRUFBRSxNQUFNLEVBQUUsT0FBTyxFQUFFLEdBQUcsT0FBTyxDQUFBO0lBQy9FLE1BQU0sSUFBSSxHQUFHLElBQUksMEJBQW1CLEVBQUUsQ0FBQTtJQUV0QyxFQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDO1FBQ1QsTUFBTSxRQUFRLEdBQUcsSUFBSSxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQztnQkFDOUIsS0FBSyxFQUFFLHVCQUFlO2dCQUN0QixPQUFPLEVBQUUsQ0FBQyxFQUFFLEtBQUssRUFBRSxJQUFJLENBQUMsS0FBSyxFQUFFLE9BQU8sRUFBRSxJQUFJLENBQUMsSUFBSSxFQUFFLENBQUM7YUFDckQsQ0FBQyxDQUFDLENBQUE7UUFDSCxJQUFJLENBQUMsR0FBRyxDQUFDLFFBQVEsQ0FBQyxDQUFBO0lBQ3BCLENBQUM7SUFDRCxFQUFFLENBQUMsQ0FBQyxZQUFZLENBQUMsQ0FBQyxDQUFDO1FBRWpCLEdBQUcsQ0FBQyxDQUFDLE1BQU0sSUFBSSxJQUFJLE1BQU0sQ0FBQyxJQUFJLENBQUMsWUFBWSxDQUFDLENBQUMsQ0FBQyxDQUFDO1lBQzdDLE1BQU0sSUFBSSxHQUFHLFlBQVksQ0FBQyxJQUFJLENBQUMsQ0FBQTtZQUMvQixhQUFhLENBQUMsV0FBVyxDQUFDLFNBQVMsQ0FBQyxJQUFJLEVBQUUsSUFBSSxDQUFDLENBQUE7UUFDakQsQ0FBQztJQUNILENBQUM7SUFDRCxFQUFFLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDO1FBQ1gsR0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLElBQUksTUFBTSxDQUFDLENBQUMsQ0FBQztZQUN2QixFQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsVUFBVSxDQUFDLElBQUksQ0FBQyxJQUFJLGFBQWEsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7Z0JBQzNDLElBQUksQ0FBQyxHQUF3RCxNQUFNLENBQUMsQ0FBQyxDQUFDLENBQUE7Z0JBQ3RFLEVBQUUsQ0FBQyxDQUFDLENBQUMsS0FBSyxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7b0JBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUE7Z0JBQUMsQ0FBQztnQkFDbEMsR0FBRyxDQUFDLENBQUMsTUFBTSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQztvQkFDbEIsSUFBSSxDQUFDLEdBQUcsQ0FBQyxhQUFhLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQTtnQkFDL0IsQ0FBQztZQUNILENBQUM7UUFDSCxDQUFDO0lBQ0gsQ0FBQztJQUNELEVBQUUsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxDQUFDLENBQUM7UUFDWixJQUFJLE9BQTRCLENBQUE7UUFDaEMsSUFBSSxRQUE0QixDQUFBO1FBQ2hDLElBQUksVUFBNkMsQ0FBQTtRQUNqRCxFQUFFLENBQUMsQ0FBQyxPQUFPLE9BQU8sS0FBSyxVQUFVLENBQUMsQ0FBQyxDQUFDO1lBQ2xDLE9BQU8sR0FBRyxPQUFPLENBQUE7UUFDbkIsQ0FBQztRQUFDLElBQUksQ0FBQyxDQUFDO1lBQ04sQ0FBQyxFQUFFLE9BQU8sRUFBRSxRQUFRLEVBQUUsVUFBVSxFQUFFLEdBQUcsT0FBTyxDQUFDLENBQUE7UUFDL0MsQ0FBQztRQUNELEVBQUUsQ0FBQyxDQUFDLENBQUMsUUFBUSxDQUFDLENBQUMsQ0FBQztZQUFDLFFBQVEsR0FBRyxHQUFHLENBQUE7UUFBQyxDQUFDO1FBQ2pDLElBQUksQ0FBQyxHQUFHLENBQUMsYUFBYSxDQUFDLGVBQWUsQ0FBQyxRQUFRLENBQUMsSUFBSSxFQUFFLEVBQUUsUUFBUSxFQUFFLE9BQU8sRUFBRSxVQUFVLEVBQUUsQ0FBQyxDQUFDLENBQUE7SUFDM0YsQ0FBQztJQUNELEVBQUUsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUM7UUFDYixHQUFHLENBQUMsQ0FBQyxNQUFNLENBQUMsSUFBSSxRQUFRLENBQUMsQ0FBQyxDQUFDO1lBQ3pCLElBQUksQ0FBQyxHQUFHLENBQUMsYUFBYSxDQUFDLFdBQVcsQ0FBQyxlQUFlLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQTtRQUN4RCxDQUFDO0lBQ0gsQ0FBQztJQUNELEVBQUUsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUM7UUFDWCxHQUFHLENBQUMsQ0FBQyxNQUFNLFNBQVMsSUFBSSxNQUFNLENBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQztZQUM1QyxNQUFNLElBQUksR0FBRyxNQUFNLENBQUMsU0FBUyxDQUFDLENBQUE7WUFDOUIsSUFBSSxDQUFDLEdBQUcsQ0FDTixhQUFhLENBQUMsa0JBQWtCLENBQUMsR0FBRyxDQUFDLElBQUksRUFBRSxTQUFTLEVBQUUsSUFBSSxDQUFDLENBQzVELENBQUE7UUFDSCxDQUFDO0lBQ0gsQ0FBQztJQUVELE1BQU0sQ0FBQyxJQUFJLENBQUE7QUFDYixDQUFDO0FBeERELDBCQXdEQyIsInNvdXJjZXNDb250ZW50IjpbImltcG9ydCB7IENvbXBvc2l0ZURpc3Bvc2FibGUsIERpc3Bvc2FibGUgfSBmcm9tICdhdG9tJ1xuXG5pbXBvcnQgeyBQbHVnaW5NYW5hZ2VyIH0gZnJvbSAnLi4vcGx1Z2luLW1hbmFnZXInXG5pbXBvcnQgeyBNQUlOX01FTlVfTEFCRUwgfSBmcm9tICcuLi91dGlscydcblxuZXhwb3J0ICogZnJvbSAnLi9pbnN0YW5jZSdcblxuZXhwb3J0IGZ1bmN0aW9uIGNvbnN1bWUocGx1Z2luTWFuYWdlcjogUGx1Z2luTWFuYWdlciwgb3B0aW9uczogVVBJLklSZWdpc3RyYXRpb25PcHRpb25zKTogRGlzcG9zYWJsZSB7XG4gIGNvbnN0IHsgbmFtZSwgbWVudSwgbWVzc2FnZVR5cGVzLCBldmVudHMsIGNvbnRyb2xzLCBwYXJhbXMsIHRvb2x0aXAgfSA9IG9wdGlvbnNcbiAgY29uc3QgZGlzcCA9IG5ldyBDb21wb3NpdGVEaXNwb3NhYmxlKClcblxuICBpZiAobWVudSkge1xuICAgIGNvbnN0IG1lbnVEaXNwID0gYXRvbS5tZW51LmFkZChbe1xuICAgICAgbGFiZWw6IE1BSU5fTUVOVV9MQUJFTCxcbiAgICAgIHN1Ym1lbnU6IFt7IGxhYmVsOiBtZW51LmxhYmVsLCBzdWJtZW51OiBtZW51Lm1lbnUgfV0sXG4gICAgfV0pXG4gICAgZGlzcC5hZGQobWVudURpc3ApXG4gIH1cbiAgaWYgKG1lc3NhZ2VUeXBlcykge1xuICAgIC8vIFRPRE86IG1ha2UgZGlzcG9zYWJsZVxuICAgIGZvciAoY29uc3QgdHlwZSBvZiBPYmplY3Qua2V5cyhtZXNzYWdlVHlwZXMpKSB7XG4gICAgICBjb25zdCBvcHRzID0gbWVzc2FnZVR5cGVzW3R5cGVdXG4gICAgICBwbHVnaW5NYW5hZ2VyLm91dHB1dFBhbmVsLmNyZWF0ZVRhYih0eXBlLCBvcHRzKVxuICAgIH1cbiAgfVxuICBpZiAoZXZlbnRzKSB7XG4gICAgZm9yIChjb25zdCBrIGluIGV2ZW50cykge1xuICAgICAgaWYgKGsuc3RhcnRzV2l0aCgnb24nKSAmJiBwbHVnaW5NYW5hZ2VyW2tdKSB7XG4gICAgICAgIGxldCB2OiBVUEkuVFRleHRCdWZmZXJDYWxsYmFjayB8IFVQSS5UVGV4dEJ1ZmZlckNhbGxiYWNrW10gPSBldmVudHNba11cbiAgICAgICAgaWYgKCFBcnJheS5pc0FycmF5KHYpKSB7IHYgPSBbdl0gfVxuICAgICAgICBmb3IgKGNvbnN0IGkgb2Ygdikge1xuICAgICAgICAgIGRpc3AuYWRkKHBsdWdpbk1hbmFnZXJba10oaSkpXG4gICAgICAgIH1cbiAgICAgIH1cbiAgICB9XG4gIH1cbiAgaWYgKHRvb2x0aXApIHtcbiAgICBsZXQgaGFuZGxlcjogVVBJLlRUb29sdGlwSGFuZGxlclxuICAgIGxldCBwcmlvcml0eTogbnVtYmVyIHwgdW5kZWZpbmVkXG4gICAgbGV0IGV2ZW50VHlwZXM6IFVQSS5URXZlbnRSYW5nZVR5cGVbXSB8IHVuZGVmaW5lZFxuICAgIGlmICh0eXBlb2YgdG9vbHRpcCA9PT0gJ2Z1bmN0aW9uJykge1xuICAgICAgaGFuZGxlciA9IHRvb2x0aXBcbiAgICB9IGVsc2Uge1xuICAgICAgKHsgaGFuZGxlciwgcHJpb3JpdHksIGV2ZW50VHlwZXMgfSA9IHRvb2x0aXApXG4gICAgfVxuICAgIGlmICghcHJpb3JpdHkpIHsgcHJpb3JpdHkgPSAxMDAgfVxuICAgIGRpc3AuYWRkKHBsdWdpbk1hbmFnZXIudG9vbHRpcFJlZ2lzdHJ5LnJlZ2lzdGVyKG5hbWUsIHsgcHJpb3JpdHksIGhhbmRsZXIsIGV2ZW50VHlwZXMgfSkpXG4gIH1cbiAgaWYgKGNvbnRyb2xzKSB7XG4gICAgZm9yIChjb25zdCBpIG9mIGNvbnRyb2xzKSB7XG4gICAgICBkaXNwLmFkZChwbHVnaW5NYW5hZ2VyLm91dHB1dFBhbmVsLmFkZFBhbmVsQ29udHJvbChpKSlcbiAgICB9XG4gIH1cbiAgaWYgKHBhcmFtcykge1xuICAgIGZvciAoY29uc3QgcGFyYW1OYW1lIG9mIE9iamVjdC5rZXlzKHBhcmFtcykpIHtcbiAgICAgIGNvbnN0IHNwZWMgPSBwYXJhbXNbcGFyYW1OYW1lXVxuICAgICAgZGlzcC5hZGQoXG4gICAgICAgIHBsdWdpbk1hbmFnZXIuY29uZmlnUGFyYW1NYW5hZ2VyLmFkZChuYW1lLCBwYXJhbU5hbWUsIHNwZWMpLFxuICAgICAgKVxuICAgIH1cbiAgfVxuXG4gIHJldHVybiBkaXNwXG59XG4iXX0=