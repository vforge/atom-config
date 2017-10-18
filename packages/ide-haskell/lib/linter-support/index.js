"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const atom_1 = require("atom");
class LinterSupport {
    constructor(linter, resultDb) {
        this.linter = linter;
        this.resultDb = resultDb;
        this.update = () => {
            this.linter.deleteMessages();
            this.linter.setMessages(Array.from(this.messages()));
        };
        this.disposables = new atom_1.CompositeDisposable();
        this.disposables.add(resultDb.onDidUpdate(this.update));
    }
    destroy() {
        this.disposables.dispose();
        this.linter.dispose();
    }
    *messages() {
        for (const result of this.resultDb.results()) {
            if (result.uri && result.position) {
                yield {
                    type: result.severity === 'lint' ? 'info' : result.severity,
                    html: result.message.toHtml(true),
                    filePath: result.uri,
                    range: new atom_1.Range(result.position, result.position.translate([0, 1])),
                };
            }
        }
    }
}
exports.LinterSupport = LinterSupport;
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaW5kZXguanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi9zcmMvbGludGVyLXN1cHBvcnQvaW5kZXgudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6Ijs7QUFBQSwrQkFBaUQ7QUFHakQ7SUFFRSxZQUFvQixNQUFvQixFQUFVLFFBQW1CO1FBQWpELFdBQU0sR0FBTixNQUFNLENBQWM7UUFBVSxhQUFRLEdBQVIsUUFBUSxDQUFXO1FBVzlELFdBQU0sR0FBRyxHQUFHLEVBQUU7WUFDbkIsSUFBSSxDQUFDLE1BQU0sQ0FBQyxjQUFjLEVBQUUsQ0FBQTtZQUM1QixJQUFJLENBQUMsTUFBTSxDQUFDLFdBQVcsQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxRQUFRLEVBQUUsQ0FBQyxDQUFDLENBQUE7UUFDdEQsQ0FBQyxDQUFBO1FBYkMsSUFBSSxDQUFDLFdBQVcsR0FBRyxJQUFJLDBCQUFtQixFQUFFLENBQUE7UUFFNUMsSUFBSSxDQUFDLFdBQVcsQ0FBQyxHQUFHLENBQUMsUUFBUSxDQUFDLFdBQVcsQ0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQTtJQUN6RCxDQUFDO0lBRU0sT0FBTztRQUNaLElBQUksQ0FBQyxXQUFXLENBQUMsT0FBTyxFQUFFLENBQUE7UUFDMUIsSUFBSSxDQUFDLE1BQU0sQ0FBQyxPQUFPLEVBQUUsQ0FBQTtJQUN2QixDQUFDO0lBT08sQ0FBRSxRQUFRO1FBQ2hCLEdBQUcsQ0FBQyxDQUFDLE1BQU0sTUFBTSxJQUFJLElBQUksQ0FBQyxRQUFRLENBQUMsT0FBTyxFQUFFLENBQUMsQ0FBQyxDQUFDO1lBQzdDLEVBQUUsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxHQUFHLElBQUksTUFBTSxDQUFDLFFBQVEsQ0FBQyxDQUFDLENBQUM7Z0JBQ2xDLE1BQU07b0JBQ0osSUFBSSxFQUFFLE1BQU0sQ0FBQyxRQUFRLEtBQUssTUFBTSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxRQUFRO29CQUMzRCxJQUFJLEVBQUUsTUFBTSxDQUFDLE9BQU8sQ0FBQyxNQUFNLENBQUMsSUFBSSxDQUFDO29CQUNqQyxRQUFRLEVBQUUsTUFBTSxDQUFDLEdBQUc7b0JBQ3BCLEtBQUssRUFBRSxJQUFJLFlBQUssQ0FBQyxNQUFNLENBQUMsUUFBUSxFQUFFLE1BQU0sQ0FBQyxRQUFRLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLENBQUM7aUJBQ3JFLENBQUE7WUFDSCxDQUFDO1FBQ0gsQ0FBQztJQUNILENBQUM7Q0FDRjtBQTlCRCxzQ0E4QkMiLCJzb3VyY2VzQ29udGVudCI6WyJpbXBvcnQgeyBDb21wb3NpdGVEaXNwb3NhYmxlLCBSYW5nZSB9IGZyb20gJ2F0b20nXG5pbXBvcnQgeyBSZXN1bHRzREIgfSBmcm9tICcuLi9yZXN1bHRzLWRiJ1xuXG5leHBvcnQgY2xhc3MgTGludGVyU3VwcG9ydCB7XG4gIHByaXZhdGUgZGlzcG9zYWJsZXM6IENvbXBvc2l0ZURpc3Bvc2FibGVcbiAgY29uc3RydWN0b3IocHJpdmF0ZSBsaW50ZXI6IExpbnRlci5JbmRpZSwgcHJpdmF0ZSByZXN1bHREYjogUmVzdWx0c0RCKSB7XG4gICAgdGhpcy5kaXNwb3NhYmxlcyA9IG5ldyBDb21wb3NpdGVEaXNwb3NhYmxlKClcblxuICAgIHRoaXMuZGlzcG9zYWJsZXMuYWRkKHJlc3VsdERiLm9uRGlkVXBkYXRlKHRoaXMudXBkYXRlKSlcbiAgfVxuXG4gIHB1YmxpYyBkZXN0cm95KCkge1xuICAgIHRoaXMuZGlzcG9zYWJsZXMuZGlzcG9zZSgpXG4gICAgdGhpcy5saW50ZXIuZGlzcG9zZSgpXG4gIH1cblxuICBwdWJsaWMgdXBkYXRlID0gKCkgPT4ge1xuICAgIHRoaXMubGludGVyLmRlbGV0ZU1lc3NhZ2VzKClcbiAgICB0aGlzLmxpbnRlci5zZXRNZXNzYWdlcyhBcnJheS5mcm9tKHRoaXMubWVzc2FnZXMoKSkpXG4gIH1cblxuICBwcml2YXRlICogbWVzc2FnZXMoKSB7XG4gICAgZm9yIChjb25zdCByZXN1bHQgb2YgdGhpcy5yZXN1bHREYi5yZXN1bHRzKCkpIHtcbiAgICAgIGlmIChyZXN1bHQudXJpICYmIHJlc3VsdC5wb3NpdGlvbikge1xuICAgICAgICB5aWVsZCB7XG4gICAgICAgICAgdHlwZTogcmVzdWx0LnNldmVyaXR5ID09PSAnbGludCcgPyAnaW5mbycgOiByZXN1bHQuc2V2ZXJpdHksXG4gICAgICAgICAgaHRtbDogcmVzdWx0Lm1lc3NhZ2UudG9IdG1sKHRydWUpLFxuICAgICAgICAgIGZpbGVQYXRoOiByZXN1bHQudXJpLFxuICAgICAgICAgIHJhbmdlOiBuZXcgUmFuZ2UocmVzdWx0LnBvc2l0aW9uLCByZXN1bHQucG9zaXRpb24udHJhbnNsYXRlKFswLCAxXSkpLFxuICAgICAgICB9XG4gICAgICB9XG4gICAgfVxuICB9XG59XG4iXX0=